<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Contracts;

/**
 * Interface  LogIndexer
 *
 * Streams a log file into fixed-width index records using a format driver,
 * in O(1) memory (one record in flight at a time).
 */
interface LogIndexer
{
    /**
     * Scan the log file and yield {@see \Arcanedev\LogViewer\Index\Record} objects.
     *
     * @param  int  $fromOffset  Start byte offset (for delta scans); 0 for a full scan.
     *
     * @return iterable<\Arcanedev\LogViewer\Index\Record>
     */
    public function scan(string $logPath, FormatDriver $driver, int $fromOffset = 0): iterable;
}
