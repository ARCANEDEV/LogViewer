<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Concurrency\BuildLock;
use Arcanedev\LogViewer\Concurrency\BuildSemaphore;
use Arcanedev\LogViewer\Contracts\IndexStore;

/**
 * Class     StreamingEngineTest
 *
 * End-to-end coverage of the streaming index engine through the real container.
 *
 * @author   Yogesh Sharma <iyogesharma@gmail.com>
 */
class StreamingEngineTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var string */
    protected $date = '2099-05-05';

    /** @var string */
    protected $logPath;

    /** @var int */
    protected $total = 20000;

    /** @var \Arcanedev\LogViewer\Contracts\LogViewer */
    protected $logViewer;

    /* -----------------------------------------------------------------
     |  Setup
     | -----------------------------------------------------------------
     */

    public function setUp(): void
    {
        parent::setUp();

        $this->logViewer = $this->app->make(\Arcanedev\LogViewer\Contracts\LogViewer::class);
        $this->logPath   = realpath(__DIR__.'/fixtures/logs').'/laravel-'.$this->date.'.log';

        $this->generateLog();
    }

    public function tearDown(): void
    {
        @unlink($this->logPath);
        @unlink($this->logPath.'.idx');

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */

    /** Generate a >2 MB default-format log with a known level distribution. */
    private function generateLog(): void
    {
        $levels = ['ERROR', 'INFO', 'WARNING', 'DEBUG'];
        $fh     = fopen($this->logPath, 'wb');

        for ($i = 0; $i < $this->total; $i++) {
            $lvl = $levels[$i % count($levels)];
            $ts  = date('Y-m-d H:i:s', 1704067200 + $i);
            fwrite($fh, "[{$ts}] production.{$lvl}: Message number {$i} payload lorem ipsum dolor sit amet consectetur\n");
            fwrite($fh, "#0 /app/foo.php(42): bar()\n#1 /app/baz.php(7): qux()\n");
        }

        fclose($fh);
        clearstatcache();
    }

    private function msgNo($entry): int
    {
        return preg_match('/Message number (\d+)/', $entry->header, $m) ? (int) $m[1] : -1;
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_streams_large_files(): void
    {
        static::assertGreaterThan(2 * 1024 * 1024, filesize($this->logPath));

        $log = $this->logViewer->logForViewer($this->date);

        static::assertTrue($log->isStreaming());
        static::assertSame($this->date, $log->date);
    }

    /** @test */
    public function it_paginates_newest_first_in_flat_memory(): void
    {
        $log = $this->logViewer->logForViewer($this->date);

        // Warm the index once (its build is streaming/O(1) but we isolate the
        // page-read cost from framework + build baseline here).
        $log->paginate('all', 30, 1);

        $before = memory_get_usage(true);
        $page   = $log->paginate('all', 30, 1);
        $after  = memory_get_usage(true);

        static::assertSame($this->total, $page->total());
        static::assertCount(30, $page->items());
        static::assertSame($this->total - 1, $this->msgNo($page->items()[0]));
        static::assertSame($this->total - 2, $this->msgNo($page->items()[1]));

        // Flat memory: reading a page allocates far less than the file size,
        // even on a multi-MB log.
        static::assertLessThan(2 * 1024 * 1024, $after - $before);
    }

    /** @test */
    public function it_paginates_by_level(): void
    {
        $log  = $this->logViewer->logForViewer($this->date);
        $page = $log->paginate('error', 30, 1);

        static::assertSame($this->total / 4, $page->total());

        foreach ($page->items() as $entry) {
            static::assertSame('error', $entry->level);
        }

        // Newest error entry: highest i with i % 4 === 0.
        static::assertSame($this->total - 4, $this->msgNo($page->items()[0]));
    }

    /** @test */
    public function it_derives_stats_from_the_index_header(): void
    {
        $log   = $this->logViewer->logForViewer($this->date);
        $stats = $log->stats();

        static::assertSame($this->total, $stats['all']);
        static::assertSame($this->total / 4, $stats['error']);
        static::assertSame($this->total / 4, $stats['info']);

        // Menu/tree build without reading the whole file.
        $tree = $log->tree();
        static::assertSame($this->total / 4, $tree['warning']['count']);
    }

    /** @test */
    public function it_searches_with_classic_semantics(): void
    {
        $log = $this->logViewer->logForViewer($this->date);

        // "number 12345" appears in exactly one entry's header.
        $result = $log->search(['number', '12345'], 'all', 30, 1);

        static::assertSame(1, $result->total());
        static::assertSame(12345, $this->msgNo($result->items()[0]));
    }

    /** @test */
    public function it_deletes_the_index_when_the_log_is_deleted(): void
    {
        // Warm the index.
        $this->logViewer->logForViewer($this->date)->paginate('all', 30, 1);

        $store   = $this->app->make(IndexStore::class);
        $idxPath = $store->pathFor($this->logPath);

        static::assertFileExists($idxPath);

        $this->logViewer->delete($this->date);

        static::assertFileDoesNotExist($this->logPath);
        static::assertFileDoesNotExist($idxPath);
    }

    /** @test */
    public function it_streams_the_cross_file_stats_table(): void
    {
        // The dashboard / logs-list aggregate stats across ALL files. For our
        // large file the counts must come from the index header, not a whole
        // read — and building the table must not blow up.
        $stats = $this->logViewer->stats();

        static::assertArrayHasKey($this->date, $stats);
        static::assertSame($this->total, $stats[$this->date]['all']);
        static::assertSame($this->total / 4, $stats[$this->date]['error']);

        // Totals across files include our big file's contribution.
        static::assertGreaterThanOrEqual($this->total, $this->logViewer->total('all'));
        static::assertGreaterThanOrEqual($this->total / 4, $this->logViewer->total('error'));
    }

    /** @test */
    public function it_streams_entries_lazily_for_the_collection_api(): void
    {
        $log = $this->logViewer->logForViewer($this->date);

        $before = memory_get_usage(true);
        $count  = $log->entries('error')->count(); // iterates the streamed generator
        $after  = memory_get_usage(true);

        static::assertSame($this->total / 4, $count);
        static::assertLessThan(8 * 1024 * 1024, $after - $before);
    }

    /** @test */
    public function it_warms_the_index_via_command(): void
    {
        $store   = $this->app->make(IndexStore::class);
        $idxPath = $store->pathFor($this->logPath);

        static::assertFileDoesNotExist($idxPath);

        $this->artisan('log-viewer:index', ['date' => $this->date])
             ->assertSuccessful();

        static::assertFileExists($idxPath);
        static::assertSame($this->total, $store->header($this->logPath)->entryCount);
    }

    /** @test */
    public function it_serves_the_tail_while_another_worker_is_building(): void
    {
        // Simulate a concurrent build by holding the per-file build lock.
        $lock  = new BuildLock;
        $token = $lock->acquire($this->logPath, false);
        static::assertNotNull($token);

        try {
            $page = $this->logViewer->logForViewer($this->date)->paginate('all', 30, 1);

            // Deferred to the bounded tail: total reflects the tail window
            // (30), not the full file (20000), but is still newest-first.
            static::assertSame(30, $page->total());
            static::assertSame($this->total - 1, $this->msgNo($page->items()[0]));
        } finally {
            $lock->release($token);
        }
    }

    /** @test */
    public function it_sweeps_orphan_index_files(): void
    {
        $store = $this->app->make(IndexStore::class);

        // Warm: indexes our large log and sweeps (no orphan yet).
        $this->artisan('log-viewer:index')->assertSuccessful();
        static::assertFileExists($store->pathFor($this->logPath));

        // A stray index whose source log does not exist.
        $orphan = dirname($this->logPath).DIRECTORY_SEPARATOR.'laravel-1999-01-01.log.idx';
        file_put_contents($orphan, 'garbage');

        // Warm again: the orphan is swept, the valid index is kept.
        $this->artisan('log-viewer:index')->assertSuccessful();

        static::assertFileDoesNotExist($orphan);
        static::assertFileExists($store->pathFor($this->logPath));

        @unlink($orphan);
    }

    /** @test */
    public function build_lock_is_single_flight(): void
    {
        $a = new BuildLock;
        $b = new BuildLock;

        $t1 = $a->acquire('some-key', false);
        static::assertNotNull($t1);

        // Held elsewhere => non-blocking acquire fails.
        static::assertNull($b->acquire('some-key', false));

        $a->release($t1);

        // Freed => acquirable again.
        $t2 = $b->acquire('some-key', false);
        static::assertNotNull($t2);
        $b->release($t2);
    }

    /** @test */
    public function build_semaphore_caps_concurrency(): void
    {
        $sem  = new BuildSemaphore(2);
        $sem2 = new BuildSemaphore(2);

        $a = $sem->acquire();
        $b = $sem->acquire();
        static::assertNotNull($a);
        static::assertNotNull($b);

        // Both slots taken => full.
        static::assertNull($sem2->acquire());

        $sem->release($a);

        // A slot freed => acquirable.
        $c = $sem2->acquire();
        static::assertNotNull($c);

        $sem->release($b);
        $sem2->release($c);
    }
}

