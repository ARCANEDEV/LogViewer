<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\LogViewer;

/**
 * Class     LogViewerTest
 *
 * @package  Arcanedev\LogViewer\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\LogViewer */
    private $logViewer;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('log-viewer.parse-all-files-in-log-path', false);

        $this->logViewer = $this->app->make(\Arcanedev\LogViewer\Contracts\LogViewer::class);
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
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(LogViewer::class,  $this->logViewer);
    }

    /** @test */
    public function it_can_be_instantiated_with_helper()
    {
        static::assertInstanceOf(LogViewer::class, log_viewer());
    }

    /** @test */
    public function it_can_get_logs_count()
    {
        static::assertSame(2, $this->logViewer->count());
    }

    /** @test */
    public function it_can_get_entries_total()
    {
        static::assertSame(16, $this->logViewer->total());
    }

    /** @test */
    public function it_can_get_entries_total_by_level()
    {
        foreach (self::$logLevels as $level) {
            static::assertSame(2, $this->logViewer->total($level));
        }
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        $logs = $this->logViewer->all();

        static::assertCount(2, $logs);
        static::assertSame(2, $logs->count());

        foreach ($logs as $log) {
            /** @var Log $log */
            $entries = $log->entries();

            static::assertDate($log->date);
            static::assertCount(8, $entries);
            static::assertSame(8, $entries->count());
            static::assertLogEntries($log->date, $entries);
        }
    }

    /** @test */
    public function it_can_paginate_all_logs()
    {
        $logs = $this->logViewer->paginate();

        static::assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $logs);
        static::assertSame(30, $logs->perPage());
        static::assertSame(2, $logs->total());
        static::assertSame(1, $logs->lastPage());
        static::assertSame(1, $logs->currentPage());
    }

    /** @test */
    public function it_can_get_log_entries()
    {
        $entries = $this->logViewer->entries($date = '2015-01-01');

        static::assertCount(8, $entries);
        static::assertSame(8, $entries->count());
        static::assertLogEntries($date, $entries);
    }

    /** @test */
    public function it_can_get_log_entries_by_level()
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
    public function it_can_delete_a_log_file()
    {
        static::createDummyLog($date = date('Y-m-d'));

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
    public function it_can_get_log_dates()
    {
        $dates = $this->logViewer->dates();

        static::assertCount(2, $dates);
        static::assertDates($dates);
    }

    /** @test */
    public function it_can_get_log_files()
    {
        $files = $this->logViewer->files();

        static::assertCount(2, $files);
        foreach ($files as $file) {
            static::assertFileExists($file);
        }
    }

    /** @test */
    public function it_can_get_all_levels()
    {
        $levels = $this->logViewer->levels();

        static::assertCount(8, $levels);
        static::assertEquals(self::$logLevels, $levels);
    }

    /** @test */
    public function it_can_get_all_translated_levels()
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
    public function it_can_get_stats()
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
    public function it_can_get_tree()
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
    public function it_can_get_translated_menu()
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
    public function it_can_download_log_file()
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
    public function it_can_check_is_not_empty()
    {
        static::assertFalse($this->logViewer->isEmpty());
    }

    /** @test */
    public function it_can_get_version()
    {
        static::assertEquals(LogViewer::VERSION, $this->logViewer->version());
    }

    /** @test */
    public function it_can_set_custom_storage_path()
    {
        $this->logViewer->setPath(storage_path('custom-path-logs'));

        $dates = $this->logViewer->dates();

        static::assertCount(1, $dates);
        static::assertDates($dates);

        static::assertEquals('2015-01-03', head($dates));
    }

    /** @test */
    public function it_can_set_and_get_pattern()
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
}
