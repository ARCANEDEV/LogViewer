<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Concurrency;

/**
 * Class     BuildLock
 *
 * Per-file single-flight lock (via `flock`) so the same log is never indexed by
 * two workers at once. Non-blocking by default: the caller that fails to acquire
 * can fall back to the tail reader instead of piling up behind the build.
 */
class BuildLock
{
    /** @var string Directory holding lock files. */
    protected $dir;

    /** @var array<string,resource> Open lock handles keyed by lock path. */
    protected $handles = [];

    public function __construct(?string $dir = null)
    {
        $this->dir = $dir ?: sys_get_temp_dir().DIRECTORY_SEPARATOR.'log-viewer-locks';

        if (! is_dir($this->dir)) {
            @mkdir($this->dir, 0755, true);
        }
    }

    /**
     * Acquire the lock for a key. Returns a token to pass to release(), or null
     * when non-blocking and the lock is held elsewhere.
     *
     * @return string|null
     */
    public function acquire(string $key, bool $blocking = false)
    {
        $path   = $this->dir.DIRECTORY_SEPARATOR.'build-'.md5($key).'.lock';
        $handle = fopen($path, 'c');

        if ($handle === false) {
            return null;
        }

        $flags = LOCK_EX | ($blocking ? 0 : LOCK_NB);

        if (! flock($handle, $flags)) {
            fclose($handle);

            return null;
        }

        $this->handles[$path] = $handle;

        return $path;
    }

    /**
     * Release a previously acquired lock.
     *
     * @param  string|null  $token
     */
    public function release($token): void
    {
        if ($token !== null && isset($this->handles[$token])) {
            flock($this->handles[$token], LOCK_UN);
            fclose($this->handles[$token]);
            unset($this->handles[$token]);
        }
    }
}
