<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Index;

/**
 * Class     LevelCodec
 *
 * Maps a PSR log level string to/from a stable 1-byte index for the binary index.
 * The order is fixed and MUST NOT change once released — it is written on disk.
 */
final class LevelCodec
{
    /**
     * Fixed on-disk ordering (index = byte value). Do not reorder.
     */
    const LEVELS = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];

    /** Sentinel for an entry whose level could not be determined. */
    const UNKNOWN = 255;

    public static function count(): int
    {
        return count(self::LEVELS);
    }

    /**
     * Level string => byte index (or UNKNOWN).
     */
    public static function toByte(?string $level): int
    {
        if ($level === null) {
            return self::UNKNOWN;
        }

        $i = array_search(strtolower($level), self::LEVELS, true);

        return $i === false ? self::UNKNOWN : $i;
    }

    /**
     * Byte index => level string (or null for UNKNOWN / out of range).
     */
    public static function toLevel(int $byte): ?string
    {
        return self::LEVELS[$byte] ?? null;
    }
}
