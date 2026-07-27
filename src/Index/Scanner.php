<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Index;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Contracts\LogIndexer;

/**
 * Class     Scanner
 *
 * Streaming log scanner. Walks the file line by line (buffered by the stream
 * layer), detecting entry boundaries via the driver and emitting fixed-width
 * {@see Record}s. Memory stays O(1): only the current entry's opening line is
 * held; entry length is pure offset arithmetic, so the multi-line stack is
 * never buffered.
 */
class Scanner implements LogIndexer
{
    /**
     * {@inheritdoc}
     */
    public function scan(string $logPath, FormatDriver $driver, int $fromOffset = 0): iterable
    {
        $size = filesize($logPath);

        if ($size === false || $size === 0) {
            return;
        }

        $fh = fopen($logPath, 'rb');

        if ($fh === false) {
            return;
        }

        try {
            if ($fromOffset > 0) {
                fseek($fh, $fromOffset);
            }

            $entryStart = null;   // byte offset of the open entry
            $firstLine  = null;   // opening line of the open entry (for level/ts)
            $pos        = $fromOffset;

            while (($line = fgets($fh)) !== false) {
                $lineStart = $pos;
                $pos       = ftell($fh);

                if ($driver->isBoundary($line)) {
                    if ($entryStart !== null) {
                        yield $this->makeRecord($driver, $entryStart, $lineStart - $entryStart, $firstLine);
                    }

                    $entryStart = $lineStart;
                    $firstLine  = $line;
                }
                // else: continuation (stack line) — advances offset only.
            }

            // Close the trailing entry.
            if ($entryStart !== null) {
                yield $this->makeRecord($driver, $entryStart, $size - $entryStart, $firstLine);
            }
        } finally {
            fclose($fh);
        }
    }

    /**
     * Build a record from the entry's opening line and its byte span.
     */
    private function makeRecord(FormatDriver $driver, int $offset, int $len, string $firstLine): Record
    {
        return new Record(
            $offset,
            $len,
            $driver->timestampOf($firstLine) ?? 0,
            LevelCodec::toByte($driver->levelOf($firstLine))
        );
    }
}
