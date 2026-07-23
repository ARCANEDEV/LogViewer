<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Reader;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Contracts\IndexStore;
use Arcanedev\LogViewer\Index\IndexManager;
use Arcanedev\LogViewer\Index\LevelCodec;
use Arcanedev\LogViewer\Index\Record;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class     PageReader
 *
 * Serves one page of log entries newest-first by seeking to just that page's
 * records in the index and reading only those entries from the log. Memory is
 * O(perPage) regardless of file size.
 *
 * Level filtering uses a bounded reverse-chunk scan of the compact record array
 * (17 bytes/entry) — O(index), never a full read of the log.
 */
class PageReader
{
    /** Records read per chunk during a filtered reverse scan. */
    const CHUNK = 8192;

    /** Max entries pulled from the tail while an index is still warming. */
    const TAIL_LIMIT = 1000;

    /** @var \Arcanedev\LogViewer\Contracts\IndexStore */
    protected $store;

    /** @var \Arcanedev\LogViewer\Index\IndexManager */
    protected $manager;

    /** @var \Arcanedev\LogViewer\Reader\TailReader */
    protected $tail;

    public function __construct(IndexStore $store, IndexManager $manager, TailReader $tail)
    {
        $this->store   = $store;
        $this->manager = $manager;
        $this->tail    = $tail;
    }

    /**
     * Read a page of entries, newest-first.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function page(
        string $logPath,
        FormatDriver $driver,
        string $level = 'all',
        int $page = 1,
        int $perPage = 30
    ): LengthAwarePaginator {
        $page   = max(1, $page);
        $header = $this->manager->ensureReady($logPath, $driver);

        // Index still warming (another worker holds the build) — serve the tail.
        if ($header === null) {
            return $this->tailPage($logPath, $driver, $level, $page, $perPage);
        }

        if ($level === 'all') {
            $total   = $header->entryCount;
            $records = $this->allPage($logPath, $header->entryCount, $page, $perPage);
        }
        else {
            $byte    = LevelCodec::toByte($level);
            $total   = $header->levelCounts[$byte] ?? 0;
            $records = $this->levelPage($logPath, $header->entryCount, $byte, $page, $perPage);
        }

        $entries = $this->readEntries($logPath, $driver, $records);

        return $this->paginator($entries, $total, $perPage, $page);
    }

    /**
     * Lazily yield every entry, in file order, in O(1) memory.
     *
     * Backs the streaming Log's entries() so cross-file aggregation and the
     * programmatic API stay memory-safe on large files.
     *
     * @return \Generator<\Arcanedev\LogViewer\Contracts\LogEntryInterface>
     */
    public function stream(string $logPath, FormatDriver $driver, string $level = 'all'): \Generator
    {
        $header = $this->manager->ensureReady($logPath, $driver);

        // Warming: yield the bounded tail (newest-first) rather than block.
        if ($header === null) {
            foreach ($this->tail->entries($logPath, $driver, self::TAIL_LIMIT) as $entry) {
                if ($level === 'all' || $entry->level === $level) {
                    yield $entry;
                }
            }

            return;
        }

        $byte  = $level === 'all' ? null : LevelCodec::toByte($level);
        $count = $header->entryCount;
        $fh    = fopen($logPath, 'rb');

        if ($fh === false) {
            return;
        }

        try {
            for ($start = 0; $start < $count; $start += self::CHUNK) {
                $records = $this->store->records($logPath, $start, min(self::CHUNK, $count - $start));

                foreach ($records as $record) {
                    if ($byte !== null && $record->level !== $byte) {
                        continue;
                    }

                    fseek($fh, $record->offset);
                    $blob = fread($fh, $record->len);

                    if (! is_string($blob)) {
                        continue;
                    }

                    foreach ($driver->parse($blob) as $entry) {
                        yield $entry;
                    }
                }
            }
        } finally {
            fclose($fh);
        }
    }

    /**
     * Transient page served from the bounded tail while the index warms.
     */
    protected function tailPage(string $logPath, FormatDriver $driver, string $level, int $page, int $perPage): LengthAwarePaginator
    {
        $limit   = min($page * $perPage, self::TAIL_LIMIT);
        $entries = $this->tail->entries($logPath, $driver, $limit);

        if ($level !== 'all') {
            $entries = array_values(array_filter($entries, function ($entry) use ($level) {
                return $entry->level === $level;
            }));
        }

        $slice = array_slice($entries, ($page - 1) * $perPage, $perPage);

        return $this->paginator($slice, count($entries), $perPage, $page);
    }

    /**
     * Record slice for the "all levels" page (direct index math), newest-first.
     *
     * @return array<int,Record>
     */
    protected function allPage(string $logPath, int $count, int $page, int $perPage): array
    {
        $endExclusive = $count - ($page - 1) * $perPage;
        $start        = max(0, $endExclusive - $perPage);
        $take         = max(0, $endExclusive - $start);

        if ($take === 0) {
            return [];
        }

        return array_reverse($this->store->records($logPath, $start, $take));
    }

    /**
     * Record slice for a single level, newest-first, via bounded reverse scan.
     *
     * @return array<int,Record>
     */
    protected function levelPage(string $logPath, int $count, int $byte, int $page, int $perPage): array
    {
        $needSkip  = ($page - 1) * $perPage;
        $collected = [];
        $skipped   = 0;

        for ($chunkEnd = $count; $chunkEnd > 0; $chunkEnd -= self::CHUNK) {
            $chunkStart = max(0, $chunkEnd - self::CHUNK);
            $chunk      = $this->store->records($logPath, $chunkStart, $chunkEnd - $chunkStart);

            for ($i = count($chunk) - 1; $i >= 0; $i--) {
                if ($chunk[$i]->level !== $byte) {
                    continue;
                }

                if ($skipped < $needSkip) {
                    $skipped++;
                    continue;
                }

                $collected[] = $chunk[$i];

                if (count($collected) >= $perPage) {
                    return $collected;
                }
            }
        }

        return $collected;
    }

    /**
     * Seek to each record and parse only that entry's bytes.
     *
     * @param  array<int,Record>  $records
     * @return array<int,\Arcanedev\LogViewer\Contracts\LogEntryInterface>
     */
    protected function readEntries(string $logPath, FormatDriver $driver, array $records): array
    {
        if (empty($records)) {
            return [];
        }

        $entries = [];
        $fh      = fopen($logPath, 'rb');

        if ($fh === false) {
            return [];
        }

        try {
            foreach ($records as $record) {
                fseek($fh, $record->offset);
                $blob = fread($fh, $record->len);

                if (! is_string($blob)) {
                    continue;
                }

                foreach ($driver->parse($blob) as $entry) {
                    $entries[] = $entry;
                }
            }
        } finally {
            fclose($fh);
        }

        return $entries;
    }

    /**
     * Wrap the page entries in a paginator matching the existing views.
     */
    protected function paginator(array $entries, int $total, int $perPage, int $page): LengthAwarePaginator
    {
        $path = '/';

        if (function_exists('app') && app()->bound('request')) {
            $path = app('request')->url();
        }

        return new LengthAwarePaginator($entries, $total, $perPage, $page, compact('path'));
    }
}
