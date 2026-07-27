<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Index;

/**
 * Class     Record
 *
 * One fixed-width index record pointing at an entry inside the source log:
 *   offset:u64 (8) · len:u32 (4) · ts:u32 (4) · level:u8 (1)  => 17-byte stride.
 *
 * Little-endian throughout so the index is portable across hosts.
 */
final class Record
{
    /** Fixed byte width of a packed record. */
    const STRIDE = 17;

    /** @var int Byte offset of the entry in the source log. */
    public $offset;

    /** @var int Byte length of the entry. */
    public $len;

    /** @var int Unix timestamp (0 when unknown). */
    public $ts;

    /** @var int Level byte (see LevelCodec). */
    public $level;

    public function __construct(int $offset, int $len, int $ts, int $level)
    {
        $this->offset = $offset;
        $this->len    = $len;
        $this->ts     = $ts;
        $this->level  = $level;
    }

    /**
     * Pack into the 17-byte on-disk representation.
     */
    public function pack(): string
    {
        return pack('P', $this->offset)
             . pack('V', $this->len)
             . pack('V', $this->ts)
             . pack('C', $this->level);
    }

    /**
     * Unpack a 17-byte record. Returns null when the blob is the wrong size.
     */
    public static function unpack(string $bytes): ?self
    {
        if (strlen($bytes) !== self::STRIDE) {
            return null;
        }

        $offset = unpack('P', substr($bytes, 0, 8))[1];
        $len    = unpack('V', substr($bytes, 8, 4))[1];
        $ts     = unpack('V', substr($bytes, 12, 4))[1];
        $level  = unpack('C', substr($bytes, 16, 1))[1];

        return new self($offset, $len, $ts, $level);
    }

    /**
     * The level string, or null when unknown.
     */
    public function levelName(): ?string
    {
        return LevelCodec::toLevel($this->level);
    }
}
