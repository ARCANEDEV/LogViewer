<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Reader;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Contracts\IndexStore;
use Arcanedev\LogViewer\Index\IndexManager;
use Arcanedev\LogViewer\Index\LevelCodec;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Class     SearchReader
 *
 * Free-text search with the exact semantics of the classic in-memory search
 * (all needles must appear in the header, stack, or context of an entry), but
 * in O(perPage) memory.
 *
 * Speedups that preserve results:
 *   - level narrowing: when a level is given, records of other levels are
 *     skipped using the index (no file read at all);
 *   - raw-bytes prefilter: a cheap all-needles substring test on the entry's
 *     raw block (a superset of the parsed fields, so no false negatives) runs
 *     before the expensive parse — only survivors are parsed and confirmed.
 */
class SearchReader
{
    /** Records scanned per chunk. */
    const CHUNK = 8192;

    /** Max entries searched from the tail while an index is still warming. */
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
     * Search entries, newest-first.
     *
     * @param  array<int,string>  $needles  Lowercased search terms.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function search(
        string $logPath,
        FormatDriver $driver,
        array $needles,
        string $level = 'all',
        int $perPage = 30,
        int $page = 1
    ): LengthAwarePaginator {
        $page      = max(1, $page);
        $header    = $this->manager->ensureReady($logPath, $driver);

        // Index still warming — search the bounded tail instead.
        if ($header === null) {
            return $this->tailSearch($logPath, $driver, $needles, $level, $perPage, $page);
        }

        $count     = $header->entryCount;
        $byte      = $level === 'all' ? null : LevelCodec::toByte($level);
        $needSkip  = ($page - 1) * $perPage;

        $matches   = 0;   // total matches (for the paginator total)
        $collected = [];  // entries for the requested page

        $fh = fopen($logPath, 'rb');

        if ($fh === false) {
            return $this->paginator([], 0, $perPage, $page);
        }

        try {
            // Walk records newest-first.
            for ($chunkEnd = $count; $chunkEnd > 0; $chunkEnd -= self::CHUNK) {
                $chunkStart = max(0, $chunkEnd - self::CHUNK);
                $chunk      = $this->store->records($logPath, $chunkStart, $chunkEnd - $chunkStart);

                for ($i = count($chunk) - 1; $i >= 0; $i--) {
                    $record = $chunk[$i];

                    if ($byte !== null && $record->level !== $byte) {
                        continue;
                    }

                    fseek($fh, $record->offset);
                    $blob = fread($fh, $record->len);

                    if (! is_string($blob) || ! $this->prefilter($blob, $needles)) {
                        continue;
                    }

                    if (! $this->confirm($driver, $blob, $needles)) {
                        continue;
                    }

                    // Confirmed match.
                    if ($matches >= $needSkip && count($collected) < $perPage) {
                        foreach ($driver->parse($blob) as $entry) {
                            $collected[] = $entry;
                        }
                    }

                    $matches++;
                }
            }
        } finally {
            fclose($fh);
        }

        return $this->paginator($collected, $matches, $perPage, $page);
    }

    /**
     * Transient search over the bounded tail while the index warms.
     */
    protected function tailSearch(string $logPath, FormatDriver $driver, array $needles, string $level, int $perPage, int $page): LengthAwarePaginator
    {
        $entries = $this->tail->entries($logPath, $driver, self::TAIL_LIMIT);

        $matched = array_values(array_filter($entries, function ($entry) use ($needles, $level) {
            if ($level !== 'all' && $entry->level !== $level) {
                return false;
            }

            foreach ([$entry->header, $entry->stack, $entry->context()] as $subject) {
                if (Str::containsAll(Str::lower((string) $subject), $needles)) {
                    return true;
                }
            }

            return false;
        }));

        $slice = array_slice($matched, ($page - 1) * $perPage, $perPage);

        return $this->paginator($slice, count($matched), $perPage, $page);
    }

    /**
     * Cheap superset check: every needle appears somewhere in the raw block.
     */
    protected function prefilter(string $blob, array $needles): bool
    {
        $haystack = strtolower($blob);

        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($haystack, $needle) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Exact classic check: all needles present in header, stack, or context.
     */
    protected function confirm(FormatDriver $driver, string $blob, array $needles): bool
    {
        foreach ($driver->parse($blob) as $entry) {
            foreach ([$entry->header, $entry->stack, $entry->context()] as $subject) {
                if (Str::containsAll(Str::lower((string) $subject), $needles)) {
                    return true;
                }
            }
        }

        return false;
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
