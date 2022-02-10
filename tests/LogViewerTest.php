<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Contracts\LogViewer as LogViewerContract;
use Arcanedev\LogViewer\LogViewer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;

/**
 * Class     LogViewerTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    private LogViewerContract $logViewer;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->logViewer = $this->app->make(LogViewerContract::class);
    }

    protected function tearDown(): void
    {
        unset($this->logViewer);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated(): void
    {
        static::assertInstanceOf(LogViewer::class,  $this->logViewer);
    }

    /** @test */
    public function it_can_be_instantiated_with_helper(): void
    {
        static::assertInstanceOf(LogViewer::class, log_viewer());
    }

    /** @test */
    public function it_can_get_logs_count(): void
    {
        static::assertSame(2, $this->logViewer->count());
    }

    /** @test */
    public function it_can_get_entries_total(): void
    {
        static::assertSame(16, $this->logViewer->total());
    }

    /** @test */
    public function it_can_get_entries_total_by_level(): void
    {
        foreach (self::$logLevels as $level) {
            static::assertSame(2, $this->logViewer->total($level));
        }
    }

    /** @test */
    public function it_can_get_all_logs(): void
    {
        $logs = $this->logViewer->all();

        static::assertCount(2, $logs);
        static::assertSame(2, $logs->count());

        foreach ($logs as $log) {
            $entries = $log->entries();

            static::assertDate($log->date);
            static::assertCount(8, $entries);
            static::assertSame(8, $entries->count());
            static::assertLogEntries($log->date, $entries);
        }
    }

    /** @test */
    public function it_can_paginate_all_logs(): void
    {
        $logs = $this->logViewer->paginate();

        static::assertInstanceOf(LengthAwarePaginator::class, $logs);
        static::assertSame(30, $logs->perPage());
        static::assertSame(2, $logs->total());
        static::assertSame(1, $logs->lastPage());
        static::assertSame(1, $logs->currentPage());
    }

    /** @test */
    public function it_can_get_log_entries(): void
    {
        $entries = $this->logViewer->entries($date = '2015-01-01');

        static::assertCount(8, $entries);
        static::assertSame(8, $entries->count());
        static::assertLogEntries($date, $entries);
    }

    /** @test */
    public function it_can_get_log_entries_by_level(): void
    {
        $date = '2015-01-01';

        foreach (self::$logLevels as $level) {
            $entries = $this->logViewer->entries($date, $level);

            static::assertCount(1, $entries);
            static::assertSame(1, $entries->count());
            static::assertLogEntries($date, $entries);
        }
    }

    /** @test */
    public function it_can_delete_a_log_file(): void
    {
        $path = storage_path('logs-to-clear');

        $this->setupLogViewerPath($path);

        static::createDummyLog($date = date('Y-m-d'), $path);

        // Assert log exists
        $entries = $this->logViewer->get($date);

        static::assertNotEmpty($entries);

        // Assert log deletion
        try {
            $deleted = $this->logViewer->delete($date);
            $message = '';
        }
        catch (\Exception $e) {
            $deleted = false;
            $message = $e->getMessage();
        }

        static::assertTrue($deleted, $message);
    }

    /** @test */
    public function it_can_get_log_dates(): void
    {
        $dates = $this->logViewer->dates();

        static::assertCount(2, $dates);
        static::assertDates($dates);
    }

    /** @test */
    public function it_can_get_log_files(): void
    {
        $files = $this->logViewer->files();

        static::assertCount(2, $files);
        foreach ($files as $file) {
            static::assertFileExists($file);
        }
    }

    /** @test */
    public function it_can_get_all_levels(): void
    {
        $levels = $this->logViewer->levels();

        static::assertCount(8, $levels);
        static::assertEquals(self::$logLevels, $levels);
    }

    /** @test */
    public function it_can_get_all_translated_levels(): void
    {
        static::assertTranslatedLevels(
            $this->app->getLocale(),
            $this->logViewer->levelsNames()
        );

        static::assertTranslatedLevels(
            $this->app->getLocale(),
            $this->logViewer->levelsNames('auto')
        );

        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            static::assertTranslatedLevels(
                $locale,
                $this->logViewer->levelsNames($locale)
            );
        }
    }

    /** @test */
    public function it_can_get_stats(): void
    {
        foreach ($this->logViewer->stats() as $date => $levels) {
            static::assertDate($date);

            foreach ($levels as $level => $count) {
                if ($level == 'all') {
                    static::assertEquals(8, $count);
                }
                else {
                    static::assertEquals(1, $count);
                    static::assertInLogLevels($level);
                }
            }
        }
    }

    /** @test */
    public function it_can_get_tree(): void
    {
        $tree = $this->logViewer->tree();

        static::assertCount(2, $tree);

        foreach ($tree as $date => $counters) {
            static::assertDate($date);

            foreach ($counters as $level => $entry) {
                if ($level === 'all') {
                    static::assertEquals($level, $entry['name']);
                    static::assertEquals(8, $entry['count']);
                }
                else {
                    static::assertInLogLevels($level);
                    static::assertEquals($level, $entry['name']);
                    static::assertEquals(1, $entry['count']);
                }
            }
        }
    }

    /** @test */
    public function it_can_get_translated_menu(): void
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);
            $menu = $this->logViewer->menu();

            static::assertCount(2, $menu);

            foreach ($menu as $date => $counters) {
                static::assertDate($date);

                foreach ($counters as $level => $entry) {
                    if ($level === 'all') {
                        static::assertTranslatedLevel($locale, $level, $entry['name']);
                        static::assertEquals(8, $entry['count']);
                    }
                    else {
                        static::assertInLogLevels($level);
                        static::assertTranslatedLevel($locale, $level, $entry['name']);
                        static::assertEquals(1, $entry['count']);
                    }
                }
            }
        }
    }

    /** @test */
    public function it_can_download_log_file(): void
    {
        $download = $this->logViewer->download($date = '2015-01-01');
        $ext      = 'log';
        $file     = $download->getFile();

        static::assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class,
            $download
        );

        static::assertFalse($download->isEmpty());
        static::assertFalse($download->isInvalid());

        static::assertEquals($ext, $file->getExtension());
        static::assertEquals("laravel-$date.$ext", $file->getBasename());
        static::assertGreaterThan(0, $file->getSize());
    }

    /** @test */
    public function it_can_check_is_not_empty(): void
    {
        static::assertFalse($this->logViewer->isEmpty());
    }

    /** @test */
    public function it_can_get_version(): void
    {
        static::assertEquals(LogViewer::VERSION, $this->logViewer->version());
    }

    /** @test */
    public function it_can_set_custom_storage_path(): void
    {
        $this->setupLogViewerPath(
            static::fixturePath('custom-path-logs')
        );

        $dates = $this->logViewer->dates();

        static::assertCount(1, $dates);
        static::assertDates($dates);

        static::assertEquals('2015-01-03', head($dates));
    }

    /** @test */
    public function it_can_set_and_get_pattern(): void
    {
        $prefix    = 'laravel-';
        $date      = '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]';
        $extension = '.log';

        static::assertSame(
            $prefix.$date.$extension,
            $this->logViewer->getPattern()
        );

        $this->logViewer->setPattern($prefix, $date, $extension = '');

        static::assertSame(
            $prefix.$date.$extension,
            $this->logViewer->getPattern()
        );

        $this->logViewer->setPattern($prefix = 'laravel-cli-', $date, $extension);

        static::assertSame(
            $prefix.$date.$extension,
            $this->logViewer->getPattern()
        );

        $this->logViewer->setPattern($prefix, $date = '[0-9][0-9][0-9][0-9]', $extension);

        static::assertSame(
            $prefix.$date.$extension,
            $this->logViewer->getPattern()
        );

        $this->logViewer->setPattern();

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->logViewer->getPattern()
        );

        $this->logViewer->setPattern(
            'laravel-', '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]', '.log'
        );

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->logViewer->getPattern()
        );
    }

    /**
     * Sets the log storage path temporarily to a new directory
     */
    private function setupLogViewerPath(string $path): void
    {
        File::ensureDirectoryExists($path);

        $this->logViewer->setPath($path);

        $this->app['config']->set(['log-viewer.storage-path' => $path]);
    }
}
