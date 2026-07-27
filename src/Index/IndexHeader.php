<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Index;

/**
 * Class     IndexHeader
 *
 * Fixed-width header at the front of every `.idx` file.
 *
 * Layout (little-endian, 122 bytes):
 *   magic          4   "LVIX"
 *   format_version 2   u16
 *   driver         16  ascii, null-padded
 *   indexed_size   8   u64   bytes of the source scanned
 *   mtime          8   u64   source mtime at index time
 *   entry_count    8   u64
 *   level_counts   64  8 x u64  (see LevelCodec order)
 *   postings_offset 8  u64   byte offset of the postings section (0 = none; reserved)
 *   checksum       4   u32   crc32 of all preceding bytes
 */
final class IndexHeader
{
    const MAGIC          = 'LVIX';
    const FORMAT_VERSION = 1;
    const SIZE           = 122;
    const DRIVER_WIDTH   = 16;

    /** @var string */
    public $driver;

    /** @var int */
    public $indexedSize;

    /** @var int */
    public $mtime;

    /** @var int */
    public $entryCount;

    /** @var array<int,int> level byte index => count */
    public $levelCounts;

    /** @var int */
    public $postingsOffset;

    /** @var int */
    public $formatVersion;

    public function __construct(
        string $driver,
        int $indexedSize,
        int $mtime,
        int $entryCount,
        array $levelCounts,
        int $postingsOffset = 0,
        int $formatVersion = self::FORMAT_VERSION
    ) {
        $this->driver         = $driver;
        $this->indexedSize    = $indexedSize;
        $this->mtime          = $mtime;
        $this->entryCount     = $entryCount;
        $this->levelCounts    = $levelCounts + array_fill(0, LevelCodec::count(), 0);
        $this->postingsOffset = $postingsOffset;
        $this->formatVersion  = $formatVersion;

        ksort($this->levelCounts);
    }

    /**
     * Pack the header (including trailing checksum) to its fixed byte width.
     */
    public function pack(): string
    {
        $body = self::MAGIC
              . pack('v', $this->formatVersion)
              . str_pad(substr($this->driver, 0, self::DRIVER_WIDTH), self::DRIVER_WIDTH, "\0")
              . pack('P', $this->indexedSize)
              . pack('P', $this->mtime)
              . pack('P', $this->entryCount);

        for ($i = 0; $i < LevelCodec::count(); $i++) {
            $body .= pack('P', $this->levelCounts[$i] ?? 0);
        }

        $body .= pack('P', $this->postingsOffset);

        return $body . pack('V', crc32($body));
    }

    /**
     * Unpack + validate a header blob. Returns null on bad magic / size / checksum.
     */
    public static function unpack(string $bytes): ?self
    {
        if (strlen($bytes) !== self::SIZE || substr($bytes, 0, 4) !== self::MAGIC) {
            return null;
        }

        $body     = substr($bytes, 0, self::SIZE - 4);
        $checksum = unpack('V', substr($bytes, self::SIZE - 4, 4))[1];

        if ($checksum !== crc32($body)) {
            return null;
        }

        $version = unpack('v', substr($bytes, 4, 2))[1];
        $driver  = rtrim(substr($bytes, 6, self::DRIVER_WIDTH), "\0");

        $p           = 6 + self::DRIVER_WIDTH;
        $indexedSize = unpack('P', substr($bytes, $p, 8))[1]; $p += 8;
        $mtime       = unpack('P', substr($bytes, $p, 8))[1]; $p += 8;
        $entryCount  = unpack('P', substr($bytes, $p, 8))[1]; $p += 8;

        $counts = [];
        for ($i = 0; $i < LevelCodec::count(); $i++) {
            $counts[$i] = unpack('P', substr($bytes, $p, 8))[1];
            $p += 8;
        }

        $postingsOffset = unpack('P', substr($bytes, $p, 8))[1];

        return new self($driver, $indexedSize, $mtime, $entryCount, $counts, $postingsOffset, $version);
    }

    /**
     * Total number of entries (alias for readability).
     */
    public function count(): int
    {
        return $this->entryCount;
    }

    /**
     * Level counts keyed by level name (unknown bucket omitted).
     *
     * @return array<string,int>
     */
    public function levelCountsByName(): array
    {
        $out = [];
        foreach ($this->levelCounts as $byte => $count) {
            if (($name = LevelCodec::toLevel($byte)) !== null) {
                $out[$name] = $count;
            }
        }

        return $out;
    }
}
