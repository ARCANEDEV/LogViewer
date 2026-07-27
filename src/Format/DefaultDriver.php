<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Format;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Helpers\LogParser;
use Arcanedev\LogViewer\Utilities\LogLevels;
use Illuminate\Support\Str;

/**
 * Class     DefaultDriver
 *
 * Laravel's default multiline log format: entries begin with a
 * `[YYYY-MM-DD HH:MM:SS] env.LEVEL:` header, followed by an optional stack.
 *
 * Wraps the existing {@see LogParser} so the whole-raw path stays byte-for-byte.
 */
class DefaultDriver implements FormatDriver
{
    /**
     * Matches an entry-opening line: `[<datetime>] ...`.
     */
    const BOUNDARY_PATTERN = '/^\['.LogParser::REGEX_DATETIME_PATTERN.'\]/';

    public function name(): string
    {
        return 'default';
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $raw): iterable
    {
        foreach (LogParser::parse($raw) as $entry) {
            [$level, $header, $stack] = array_values($entry);

            yield new LogEntry($level, $header, $stack);
        }
    }

    public function isBoundary(string $line): bool
    {
        return (bool) preg_match(self::BOUNDARY_PATTERN, $line);
    }

    public function levelOf(string $rawEntry): ?string
    {
        foreach (LogLevels::all() as $level) {
            if (Str::contains($rawEntry, strtoupper(".{$level}:"))) {
                return $level;
            }
        }

        return null;
    }

    public function timestampOf(string $rawEntry): ?int
    {
        if (preg_match('/\[('.LogParser::REGEX_DATETIME_PATTERN.')/', $rawEntry, $m)) {
            $ts = strtotime($m[1]);

            return $ts === false ? null : $ts;
        }

        return null;
    }

    public function supportsStreaming(): bool
    {
        return true;
    }
}
