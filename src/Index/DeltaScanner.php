<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Index;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Contracts\IndexStore;
use Arcanedev\LogViewer\Contracts\LogIndexer;

/**
 * Class     DeltaScanner
 *
 * Keeps a log file's index current with the minimum work:
 *   - no index / driver changed / file truncated  => full rebuild
 *   - size + mtime unchanged                       => already fresh, no-op
 *   - file grew                                    => rescan only from the last
 *                                                     indexed entry to EOF and
 *                                                     append (O(new bytes))
 *
 * The last indexed entry is re-scanned (not skipped) so appended stack lines
 * extend it correctly instead of being mistaken for a new entry.
 */
class DeltaScanner
{
    /** @var \Arcanedev\LogViewer\Contracts\IndexStore */
    protected $store;

    /** @var \Arcanedev\LogViewer\Contracts\LogIndexer */
    protected $scanner;

    public function __construct(IndexStore $store, LogIndexer $scanner)
    {
        $this->store   = $store;
        $this->scanner = $scanner;
    }

    /**
     * Ensure the index is up to date and return the current header.
     */
    public function sync(string $logPath, FormatDriver $driver): IndexHeader
    {
        $header = $this->store->header($logPath);
        $size   = (int) filesize($logPath);
        $mtime  = (int) filemtime($logPath);

        // No usable index, format changed, or file shrank/rotated => rebuild.
        if ($header === null
            || $header->driver !== $driver->name()
            || $size < $header->indexedSize
            || $header->entryCount === 0) {
            return $this->store->write($logPath, $driver);
        }

        // Already current.
        if ($size === $header->indexedSize && $mtime === $header->mtime) {
            return $header;
        }

        return $this->appendDelta($logPath, $driver, $header, $size, $mtime);
    }

    /**
     * Rescan from the last indexed entry to EOF and append in place.
     */
    protected function appendDelta(
        string $logPath,
        FormatDriver $driver,
        IndexHeader $header,
        int $size,
        int $mtime
    ): IndexHeader {
        $last = $this->store->records($logPath, $header->entryCount - 1, 1);

        if (empty($last)) {
            return $this->store->write($logPath, $driver);
        }

        $last    = $last[0];
        $idxPath = $this->store->pathFor($logPath);
        $fh      = fopen($idxPath, 'r+b');

        if ($fh === false) {
            return $this->store->write($logPath, $driver);
        }

        try {
            // Overwrite the last record onward.
            fseek($fh, IndexHeader::SIZE + ($header->entryCount - 1) * Record::STRIDE);

            $counts = $header->levelCounts;
            if ($last->level !== LevelCodec::UNKNOWN) {
                $counts[$last->level]--;
            }

            $count = $header->entryCount - 1;

            foreach ($this->scanner->scan($logPath, $driver, $last->offset) as $record) {
                fwrite($fh, $record->pack());
                $count++;

                if ($record->level !== LevelCodec::UNKNOWN) {
                    $counts[$record->level]++;
                }
            }

            ftruncate($fh, IndexHeader::SIZE + $count * Record::STRIDE);

            $newHeader = new IndexHeader($driver->name(), $size, $mtime, $count, $counts);
            fseek($fh, 0);
            fwrite($fh, $newHeader->pack());
        } finally {
            fclose($fh);
        }

        return $newHeader;
    }
}
