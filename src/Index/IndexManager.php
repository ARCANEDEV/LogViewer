<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Index;

use Arcanedev\LogViewer\Concurrency\BuildLock;
use Arcanedev\LogViewer\Concurrency\BuildSemaphore;
use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Contracts\IndexStore;

/**
 * Class     IndexManager
 *
 * Coordinates keeping an index current under concurrency:
 *   - fresh index                => return it, no locking;
 *   - growing file (cheap delta) => per-file lock, then append (or serve the
 *     slightly-stale index if another worker holds the lock);
 *   - cold / rebuild (O(file))   => per-file lock + a global build slot; the
 *     one worker that wins builds synchronously, everyone else is deferred
 *     (returns null) so the reader can serve the bounded tail while it warms.
 */
class IndexManager
{
    /** @var \Arcanedev\LogViewer\Contracts\IndexStore */
    protected $store;

    /** @var \Arcanedev\LogViewer\Index\DeltaScanner */
    protected $delta;

    /** @var \Arcanedev\LogViewer\Concurrency\BuildLock */
    protected $lock;

    /** @var \Arcanedev\LogViewer\Concurrency\BuildSemaphore */
    protected $semaphore;

    public function __construct(
        IndexStore $store,
        DeltaScanner $delta,
        BuildLock $lock,
        BuildSemaphore $semaphore
    ) {
        $this->store     = $store;
        $this->delta     = $delta;
        $this->lock      = $lock;
        $this->semaphore = $semaphore;
    }

    /**
     * Ensure the index is usable and return its header, or null when a build is
     * required but was deferred (caller should serve the tail).
     */
    public function ensureReady(string $logPath, FormatDriver $driver, bool $blocking = false): ?IndexHeader
    {
        $header = $this->store->header($logPath);
        $size   = (int) filesize($logPath);
        $mtime  = (int) filemtime($logPath);

        // Fresh: nothing to do, no locking.
        if ($header !== null
            && $header->driver === $driver->name()
            && $header->indexedSize === $size
            && $header->mtime === $mtime) {
            return $header;
        }

        $needsFull = $header === null
            || $header->driver !== $driver->name()
            || $size < $header->indexedSize
            || $header->entryCount === 0;

        return $needsFull
            ? $this->build($logPath, $driver, $header, $blocking)
            : $this->update($logPath, $driver, $header, $blocking);
    }

    /**
     * Force the index fully up to date (blocking). Used by the warm command.
     */
    public function warm(string $logPath, FormatDriver $driver): IndexHeader
    {
        $token = $this->lock->acquire($logPath, true);

        try {
            return $this->delta->sync($logPath, $driver);
        } finally {
            $this->lock->release($token);
        }
    }

    /**
     * Full (O(file)) build path — gated by a global slot + per-file lock.
     */
    protected function build(string $logPath, FormatDriver $driver, ?IndexHeader $header, bool $blocking): ?IndexHeader
    {
        $slot = $this->semaphore->acquire();

        if ($slot === null) {
            return null; // too many builds in flight -> serve tail
        }

        $token = $this->lock->acquire($logPath, $blocking);

        if ($token === null) {
            $this->semaphore->release($slot);

            return null; // another worker is building -> serve tail
        }

        try {
            // Double-check: another worker may have finished while we waited.
            $fresh = $this->store->header($logPath);
            if ($fresh !== null
                && $fresh->driver === $driver->name()
                && $fresh->indexedSize === (int) filesize($logPath)
                && $fresh->mtime === (int) filemtime($logPath)) {
                return $fresh;
            }

            return $this->delta->sync($logPath, $driver);
        } finally {
            $this->lock->release($token);
            $this->semaphore->release($slot);
        }
    }

    /**
     * Cheap incremental append for a growing file — per-file lock only.
     */
    protected function update(string $logPath, FormatDriver $driver, IndexHeader $header, bool $blocking): ?IndexHeader
    {
        $token = $this->lock->acquire($logPath, $blocking);

        // Someone else is updating: the existing index is still valid, just a
        // few entries behind — serve it rather than blocking or tailing.
        if ($token === null) {
            return $header;
        }

        try {
            return $this->delta->sync($logPath, $driver);
        } finally {
            $this->lock->release($token);
        }
    }
}
