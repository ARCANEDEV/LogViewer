<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Index;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Contracts\IndexStore;
use Arcanedev\LogViewer\Contracts\LogIndexer;

/**
 * Class     BinaryIndexStore
 *
 * Reads and writes the fixed-width binary `.idx` sidecar. Writes are atomic
 * (temp file + rename); reads are random-access (seek + fixed stride) so the
 * index is never loaded whole.
 */
class BinaryIndexStore implements IndexStore
{
    /** @var \Arcanedev\LogViewer\Contracts\LogIndexer */
    protected $indexer;

    /** @var string|null Directory for sidecars; null = beside the log file. */
    protected $indexPath;

    public function __construct(LogIndexer $indexer, ?string $indexPath = null)
    {
        $this->indexer   = $indexer;
        $this->indexPath = $indexPath;
    }

    /**
     * {@inheritdoc}
     */
    public function pathFor(string $logPath): string
    {
        if ($this->indexPath) {
            $name = basename($logPath).'-'.substr(md5($logPath), 0, 8).'.idx';

            return rtrim($this->indexPath, '/\\').DIRECTORY_SEPARATOR.$name;
        }

        return $logPath.'.idx';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $logPath, FormatDriver $driver): IndexHeader
    {
        $idxPath = $this->pathFor($logPath);
        $tmpPath = $idxPath.'.'.getmypid().'.tmp';

        $this->ensureDirectory($idxPath);

        $out = fopen($tmpPath, 'wb');

        if ($out === false) {
            throw new \RuntimeException("Unable to open index temp file: {$tmpPath}");
        }

        try {
            // Reserve header space; records are appended after it.
            fwrite($out, str_repeat("\0", IndexHeader::SIZE));

            $counts = array_fill(0, LevelCodec::count(), 0);
            $count  = 0;

            foreach ($this->indexer->scan($logPath, $driver) as $record) {
                fwrite($out, $record->pack());
                $count++;

                if ($record->level !== LevelCodec::UNKNOWN) {
                    $counts[$record->level]++;
                }
            }

            $header = new IndexHeader(
                $driver->name(),
                (int) filesize($logPath),
                (int) filemtime($logPath),
                $count,
                $counts
            );

            // Backfill the header now that counts are known.
            fseek($out, 0);
            fwrite($out, $header->pack());
        } finally {
            fclose($out);
        }

        // Atomic publish.
        if (! @rename($tmpPath, $idxPath)) {
            @unlink($tmpPath);
            throw new \RuntimeException("Unable to publish index: {$idxPath}");
        }

        return $header;
    }

    /**
     * {@inheritdoc}
     */
    public function header(string $logPath): ?IndexHeader
    {
        $idxPath = $this->pathFor($logPath);

        if (! is_file($idxPath)) {
            return null;
        }

        $fh = fopen($idxPath, 'rb');

        if ($fh === false) {
            return null;
        }

        try {
            $bytes = fread($fh, IndexHeader::SIZE);
        } finally {
            fclose($fh);
        }

        return is_string($bytes) ? IndexHeader::unpack($bytes) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh(string $logPath): bool
    {
        if (! is_file($logPath) || ($header = $this->header($logPath)) === null) {
            return false;
        }

        return $header->indexedSize === (int) filesize($logPath)
            && $header->mtime === (int) filemtime($logPath);
    }

    /**
     * {@inheritdoc}
     */
    public function records(string $logPath, int $from, int $count): array
    {
        if ($count <= 0 || $from < 0) {
            return [];
        }

        $idxPath = $this->pathFor($logPath);

        if (! is_file($idxPath)) {
            return [];
        }

        $fh = fopen($idxPath, 'rb');

        if ($fh === false) {
            return [];
        }

        $records = [];

        try {
            if (fseek($fh, IndexHeader::SIZE + $from * Record::STRIDE) !== 0) {
                return [];
            }

            $blob = fread($fh, $count * Record::STRIDE);

            if (! is_string($blob)) {
                return [];
            }

            foreach (str_split($blob, Record::STRIDE) as $chunk) {
                if (($record = Record::unpack($chunk)) !== null) {
                    $records[] = $record;
                }
            }
        } finally {
            fclose($fh);
        }

        return $records;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $logPath): void
    {
        $idxPath = $this->pathFor($logPath);

        if (is_file($idxPath)) {
            @unlink($idxPath);
        }
    }

    /**
     * Remove `.idx` sidecars in a directory whose source log no longer exists.
     *
     * @param  array<int,string>  $validLogPaths
     *
     * @return array<int,string>  The orphan index paths that were deleted.
     */
    public function sweepOrphans(string $dir, array $validLogPaths): array
    {
        $valid = [];
        foreach ($validLogPaths as $logPath) {
            $valid[$this->normalize($this->pathFor($logPath))] = true;
        }

        $deleted = [];
        $glob    = glob(rtrim($dir, '/\\').DIRECTORY_SEPARATOR.'*.idx') ?: [];

        foreach ($glob as $idxPath) {
            if (! isset($valid[$this->normalize($idxPath)])) {
                @unlink($idxPath);
                $deleted[] = $idxPath;
            }
        }

        return $deleted;
    }

    /**
     * Normalize a path for comparison (resolve real path when it exists).
     */
    private function normalize(string $path): string
    {
        $real = realpath($path);

        return $real !== false ? $real : $path;
    }

    /**
     * Ensure the sidecar's directory exists.
     */
    private function ensureDirectory(string $idxPath): void
    {
        $dir = dirname($idxPath);

        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}
