<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Contracts;

use Arcanedev\LogViewer\Index\IndexHeader;
use Arcanedev\LogViewer\Index\Record;

/**
 * Interface  IndexStore
 *
 * Persists and reads the binary `.idx` sidecar for a log file. Reads are
 * random-access (seek + fixed stride) so the index is never loaded whole.
 */
interface IndexStore
{
    /**
     * Absolute path of the `.idx` sidecar for a given log file.
     */
    public function pathFor(string $logPath): string;

    /**
     * Build (or rebuild) the index for a log file. Atomic (temp + rename).
     */
    public function write(string $logPath, FormatDriver $driver): IndexHeader;

    /**
     * Read + validate the header, or null when missing/corrupt.
     */
    public function header(string $logPath): ?IndexHeader;

    /**
     * Whether a valid index exists and matches the current file size + mtime.
     */
    public function isFresh(string $logPath): bool;

    /**
     * Read a slice of records by index (random access).
     *
     * @return array<int,Record>
     */
    public function records(string $logPath, int $from, int $count): array;

    /**
     * Delete the index sidecar (no-op if absent).
     */
    public function delete(string $logPath): void;

    /**
     * Remove index sidecars in a directory whose source log no longer exists.
     *
     * @param  array<int,string>  $validLogPaths
     *
     * @return array<int,string>  Deleted orphan index paths.
     */
    public function sweepOrphans(string $dir, array $validLogPaths): array;
}
