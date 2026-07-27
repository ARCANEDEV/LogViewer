<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\LogEntry;
use Psr\Log\LogLevel;

/**
 * Class     PcreSafetyTest
 *
 * Covers the linear brace-scan that replaced the recursive PCRE context
 * extractor (which silently failed on large / deeply-nested JSON).
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class PcreSafetyTest extends TestCase
{
    /** @test */
    public function it_extracts_a_simple_context(): void
    {
        $entry = new LogEntry(LogLevel::ERROR, '[2024-01-01 00:00:00] production.ERROR: Whoops {"user":{"id":5,"roles":["a","b"]}}');

        static::assertSame(['user' => ['id' => 5, 'roles' => ['a', 'b']]], json_decode($entry->context(), true));
        static::assertStringNotContainsString('{', $entry->header);
    }

    /** @test */
    public function it_extracts_deeply_nested_context_without_failing(): void
    {
        $depth = 400; // would stress the old recursive regex
        $json  = str_repeat('{"a":', $depth).'0'.str_repeat('}', $depth);

        $entry = new LogEntry(LogLevel::ERROR, "[2024-01-01 00:00:00] production.ERROR: Deep {$json}");

        $context = json_decode($entry->context(), true);

        static::assertIsArray($context);
        static::assertArrayHasKey('a', $context);
    }

    /** @test */
    public function it_extracts_a_large_context_without_failing(): void
    {
        $payload = str_repeat('x', 1024 * 1024); // 1 MB string value
        $json    = json_encode(['message' => $payload]);

        $entry = new LogEntry(LogLevel::ERROR, "[2024-01-01 00:00:00] production.ERROR: Big {$json}");

        $context = json_decode($entry->context(), true);

        static::assertIsArray($context);
        static::assertSame($payload, $context['message']);
    }

    /** @test */
    public function it_leaves_the_header_untouched_when_there_is_no_context(): void
    {
        $entry = new LogEntry(LogLevel::INFO, '[2024-01-01 00:00:00] production.INFO: Plain message with no json');

        static::assertSame('Plain message with no json', $entry->header);
        static::assertSame('[]', trim($entry->context()));
    }
}
