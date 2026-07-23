<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Concurrency;

/**
 * Class     BuildSemaphore
 *
 * Caps the number of index builds happening across the whole app at once, so a
 * traffic spike over many cold huge files can't spawn N simultaneous full
 * scans and exhaust CPU/IO. Implemented as N `flock` slot files; acquiring is
 * non-blocking — no free slot means "too busy right now", and the caller falls
 * back to the tail reader.
 */
class BuildSemaphore
{
    /** @var string */
    protected $dir;

    /** @var int */
    protected $max;

    /** @var array<string,resource> */
    protected $handles = [];

    public function __construct(int $max = 2, ?string $dir = null)
    {
        $this->max = max(1, $max);
        $this->dir = $dir ?: sys_get_temp_dir().DIRECTORY_SEPARATOR.'log-viewer-locks';

        if (! is_dir($this->dir)) {
            @mkdir($this->dir, 0755, true);
        }
    }

    /**
     * Try to take a slot. Returns a token for release(), or null when full.
     *
     * @return string|null
     */
    public function acquire()
    {
        for ($i = 0; $i < $this->max; $i++) {
            $path   = $this->dir.DIRECTORY_SEPARATOR.'sem-'.$i.'.lock';
            $handle = fopen($path, 'c');

            if ($handle === false) {
                continue;
            }

            if (flock($handle, LOCK_EX | LOCK_NB)) {
                $this->handles[$path] = $handle;

                return $path;
            }

            fclose($handle);
        }

        return null;
    }

    /**
     * Release a slot.
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
