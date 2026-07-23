<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Format;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Entities\JsonLogEntry;

/**
 * Class     JsonLinesDriver
 *
 * One JSON object per line. Each line is an independent entry, which makes the
 * format naturally streamable.
 *
 * Mirrors the existing JSON branch of {@see \Arcanedev\LogViewer\Entities\LogEntryCollection::load()}.
 */
class JsonLinesDriver implements FormatDriver
{
    public function name(): string
    {
        return 'json';
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $raw): iterable
    {
        foreach (explode("\n", $raw) as $row) {
            $entry = json_decode($row, true);

            if ($entry && gettype($entry) === 'array') {
                yield new JsonLogEntry($entry);
            }
        }
    }

    public function isBoundary(string $line): bool
    {
        // Each non-empty line is a self-contained entry.
        return trim($line) !== '';
    }

    public function levelOf(string $rawEntry): ?string
    {
        $data = json_decode($rawEntry, true);

        return (is_array($data) && isset($data['level_name']))
            ? strtolower($data['level_name'])
            : null;
    }

    public function timestampOf(string $rawEntry): ?int
    {
        $data = json_decode($rawEntry, true);

        if (is_array($data) && isset($data['datetime'])) {
            $ts = strtotime($data['datetime']);

            return $ts === false ? null : $ts;
        }

        return null;
    }

    public function supportsStreaming(): bool
    {
        return true;
    }
}
