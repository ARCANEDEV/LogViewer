<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\LogViewer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class     LogViewerTest
 *
 * @package  Arcanedev\LogViewer\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogViewer */
    private $logViewer;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->logViewer = $this->app['arcanedev.log-viewer'];
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->logViewer);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(LogViewer::class,  $this->logViewer);
    }

    /** @test */
    public function it_can_be_instantiated_with_helper()
    {
        $this->assertInstanceOf(LogViewer::class, log_viewer());
    }

    /** @test */
    public function it_can_get_logs_count()
    {
        $this->assertEquals(2, $this->logViewer->count());
    }

    /** @test */
    public function it_can_get_entries_total()
    {
        $this->assertEquals(16, $this->logViewer->total());
    }

    /** @test */
    public function it_can_get_entries_total_by_level()
    {
        foreach (self::$logLevels as $level) {
            $this->assertEquals(2, $this->logViewer->total($level));
        }
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        $logs = $this->logViewer->all();

        $this->assertCount(2, $logs);

        foreach ($logs as $log) {
            /** @var Log $log */
            $entries = $log->entries();

            $this->assertDate($log->date);
            $this->assertCount(8, $entries);
            $this->assertLogEntries($log->date, $entries);
        }
    }

    /** @test */
    public function it_can_paginate_all_logs()
    {
        $logs = $this->logViewer->paginate();
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $logs);
        $this->assertEquals(30, $logs->perPage());
        $this->assertEquals(2, $logs->total());
        $this->assertEquals(1, $logs->lastPage());
        $this->assertEquals(1, $logs->currentPage());
    }

    /** @test */
    public function it_can_get_log_entries()
    {
        $date       = '2015-01-01';
        $logEntries = $this->logViewer->entries($date);

        $this->assertCount(8, $logEntries);
        $this->assertLogEntries($date, $logEntries);
    }

    /** @test */
    public function it_can_get_log_entries_by_level()
    {
        $date       = '2015-01-01';
        foreach (self::$logLevels as $level) {
            $logEntries = $this->logViewer->entries($date, $level);

            $this->assertCount(1, $logEntries);
            $this->assertLogEntries($date, $logEntries);
        }
    }

    /** @test */
    public function it_can_delete_a_log_file()
    {
        $date = date('Y-m-d');

        $this->createDummyLog($date);

        // Assert log exists
        $logEntries = $this->logViewer->get($date);

        $this->assertNotEmpty($logEntries);

        // Assert log deletion
        try {
            $deleted = $this->logViewer->delete($date);
            $message = '';
        }
        catch (\Exception $e) {
            $deleted = false;
            $message = $e->getMessage();
        }

        $this->assertTrue($deleted, $message);
    }

    /** @test */
    public function it_can_get_log_dates()
    {
        $dates = $this->logViewer->dates();

        $this->assertCount(2, $dates);
        $this->assertDates($dates);
    }

    /** @test */
    public function it_can_get_log_files()
    {
        $files = $this->logViewer->files();

        $this->assertCount(2, $files);
        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }

    /** @test */
    public function it_can_get_all_levels()
    {
        $levels = $this->logViewer->levels();

        $this->assertCount(8, $levels);
        $this->assertEquals(self::$logLevels, $levels);
    }

    /** @test */
    public function it_can_get_all_translated_levels()
    {
        $this->assertTranslatedLevels(
            $this->app->getLocale(),
            $this->logViewer->levelsNames()
        );

        $this->assertTranslatedLevels(
            $this->app->getLocale(),
            $this->logViewer->levelsNames('auto')
        );

        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            $this->assertTranslatedLevels(
                $locale,
                $this->logViewer->levelsNames($locale)
            );
        }
    }

    /** @test */
    public function it_can_get_stats()
    {
        $stats = $this->logViewer->stats();

        foreach ($stats as $date => $levels) {
            $this->assertDate($date);

            foreach ($levels as $level => $count) {
                if ($level == 'all') {
                    $this->assertEquals(8, $count);
                    continue;
                }

                $this->assertEquals(1, $count);
                $this->assertInLogLevels($level);
            }
        }
    }

    /** @test */
    public function it_can_get_stats_table()
    {
        $table = $this->logViewer->statsTable();

        $this->assertTable($table);
    }

    /** @test */
    public function it_can_get_tree()
    {
        $tree = $this->logViewer->tree();

        $this->assertCount(2, $tree);
        foreach ($tree as $date => $counters) {
            $this->assertDate($date);

            foreach ($counters as $level => $entry) {
                if ($level === 'all') {
                    $this->assertEquals($level, $entry['name']);
                    $this->assertEquals(8, $entry['count']);

                    continue;
                }

                $this->assertInLogLevels($level);
                $this->assertEquals($level, $entry['name']);
                $this->assertEquals(1, $entry['count']);
            }
        }
    }

    /** @test */
    public function it_can_get_translated_menu()
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);
            $menu   = $this->logViewer->menu();

            $this->assertCount(2, $menu);
            foreach ($menu as $date => $counters) {
                $this->assertDate($date);

                foreach ($counters as $level => $entry) {
                    if ($level === 'all') {
                        $this->assertTranslatedLevel($locale, $level, $entry['name']);
                        $this->assertEquals(8, $entry['count']);

                        continue;
                    }

                    $this->assertInLogLevels($level);
                    $this->assertTranslatedLevel($locale, $level, $entry['name']);
                    $this->assertEquals(1, $entry['count']);
                }
            }
        }
    }

    /** @test */
    public function it_can_download_log_file()
    {
        $date     = '2015-01-01';
        $ext      = 'log';
        $download = $this->logViewer->download($date);
        $file     = $download->getFile();

        $this->assertInstanceOf(BinaryFileResponse::class, $download);
        $this->assertFalse($download->isEmpty());
        $this->assertFalse($download->isInvalid());

        $this->assertEquals($ext, $file->getExtension());
        $this->assertEquals("laravel-$date.$ext", $file->getBasename());
        $this->assertGreaterThan(0, $file->getSize());
    }
}
