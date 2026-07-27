<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Reader;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Contracts\LogIndexer;

/**
 * Class     TailReader
 *
 * Bounded fallback for a cold, un-indexed huge file. Streams the last N bytes
 * through the scanner (O(1) memory), keeps only the last `$limit` record
 * pointers in a ring buffer, then reads + parses just those few entries.
 *
 * Memory is O($limit) — never the window size and never the file size — so it
 * stays safe to show recent entries while the full index is still warming.
 */
class TailReader
{
    /** @var \Arcanedev\LogViewer\Contracts\LogIndexer */
    protected $scanner;

    /** @var int Bytes to scan back from the end of the file. */
    protected $tailBytes;

    public function __construct(LogIndexer $scanner, int $tailBytes = 26214400) // 25 MB
    {
        $this->scanner   = $scanner;
        $this->tailBytes = $tailBytes;
    }

    /**
     * Read the most recent entries, newest-first (bounded memory).
     *
     * @return array<int,\Arcanedev\LogViewer\Contracts\LogEntryInterface>
     */
    public function entries(string $logPath, FormatDriver $driver, int $limit = 100): array
    {
        $size = (int) filesize($logPath);

        if ($size === 0 || $limit <= 0) {
            return [];
        }

        $start = max(0, $size - $this->tailBytes);

        // Stream the window; keep only the last $limit record pointers.
        // The scanner drops the partial leading entry when $start is mid-entry.
        $ring = [];
        foreach ($this->scanner->scan($logPath, $driver, $start) as $record) {
            $ring[] = $record;

            if (count($ring) > $limit) {
                array_shift($ring);
            }
        }

        if (empty($ring)) {
            return [];
        }

        $entries = [];
        $fh      = fopen($logPath, 'rb');

        if ($fh === false) {
            return [];
        }

        try {
            foreach ($ring as $record) {
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

        return array_reverse($entries);
    }
}
