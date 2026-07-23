<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Contracts;

/**
 * Interface  FormatDriver
 *
 * A format driver knows how to turn a raw log file into log entries for a given
 * log format (Laravel's default multiline format, JSON-lines, or a custom one
 * registered by the host application).
 *
 * The whole-raw {@see parse()} method backs the existing (back-compat) read path.
 * The streaming primitives ({@see isBoundary()}, {@see levelOf()},
 * {@see supportsStreaming()}) back the streaming index engine.
 */
interface FormatDriver
{
    /**
     * The driver's registered name (e.g. "default", "json").
     */
    public function name(): string;

    /**
     * Parse the whole raw file content into log entries.
     *
     * Yields entries lazily to keep the existing behavior byte-for-byte.
     *
     * @return iterable<\Arcanedev\LogViewer\Contracts\LogEntryInterface>
     */
    public function parse(string $raw): iterable;

    /**
     * Whether the given line starts a new log entry.
     *
     * Used by the streaming scanner to split entries without loading the file.
     */
    public function isBoundary(string $line): bool;

    /**
     * Resolve the log level of a raw entry block, or null when undetermined.
     */
    public function levelOf(string $rawEntry): ?string;

    /**
     * Resolve the Unix timestamp of a raw entry, or null when undetermined.
     *
     * Only the opening line is needed, so this stays cheap during a streaming scan.
     */
    public function timestampOf(string $rawEntry): ?int;

    /**
     * Whether this format can be streamed (indexed) rather than read whole.
     */
    public function supportsStreaming(): bool;
}
