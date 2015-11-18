<?php namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\Factory;

/**
 * Class     FactoryTest
 *
 * @package  Arcanedev\LogViewer\Tests\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class FactoryTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var Factory */
    private $logFactory;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->logFactory = $this->app['arcanedev.log-viewer.factory'];
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->logFactory);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(
            'Arcanedev\\LogViewer\\Utilities\\Factory',
            $this->logFactory
        );
    }

    /** @test */
    public function it_can_get_filesystem_object()
    {
        $this->assertInstanceOf(
            'Arcanedev\\LogViewer\\Utilities\\Filesystem',
            $this->logFactory->getFilesystem()
        );
    }

    /** @test */
    public function it_can_get_log_entries()
    {
        $date       = '2015-01-01';
        $logEntries = $this->logFactory->entries($date);

        foreach ($logEntries as $logEntry) {
            $this->assertLogEntry($date, $logEntry);
        }
    }

    /** @test */
    public function it_can_get_dates()
    {
        $dates = $this->logFactory->dates();

        $this->assertCount(2, $dates);
        $this->assertDates($dates);
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        $logs = $this->logFactory->all();

        $this->assertInstanceOf(
            'Arcanedev\\LogViewer\\Entities\\LogCollection',
            $logs
        );
        $this->assertCount(2, $logs);
    }

    /** @test */
    public function it_can_paginate_all_logs()
    {
        $logs = $this->logFactory->paginate();

        $this->assertInstanceOf(
            'Illuminate\\Pagination\\LengthAwarePaginator',
            $logs
        );
        $this->assertEquals(30, $logs->perPage());
        $this->assertEquals(2, $logs->total());
        $this->assertEquals(1, $logs->lastPage());
        $this->assertEquals(1, $logs->currentPage());
    }

    /** @test */
    public function it_can_get_count()
    {
        $this->assertEquals(2, $this->logFactory->count());
    }

    /** @test */
    public function it_can_can_set_custom_path()
    {
        $this->logFactory->setPath(storage_path('custom-path-logs'));

        $this->assertEquals(1, $this->logFactory->count());

        $date       = '2015-01-03';
        $logEntries = $this->logFactory->entries($date);

        foreach ($logEntries as $logEntry) {
            $this->assertLogEntry($date, $logEntry);
        }
    }

    /** @test */
    public function it_can_get_total()
    {
        $this->assertEquals(16, $this->logFactory->total());
    }

    /** @test */
    public function it_can_get_total_by_level()
    {
        foreach (self::$logLevels as $level) {
            $this->assertEquals(2, $this->logFactory->total($level));
        }
    }

    /** @test */
    public function it_can_get_tree()
    {
        $tree = $this->logFactory->tree();

        foreach ($tree as $date => $levels) {
            $this->assertDate($date);

            // TODO: Complete the assertions
        }
    }

    /** @test */
    public function it_can_get_translated_tree()
    {
        // TODO: Complete the test
    }

    /** @test */
    public function it_can_get_menu()
    {
        $menu = $this->logFactory->menu();

        foreach ($menu as $date => $item) {
            $this->assertDate($date);

            // TODO: Complete the assertions
        }
    }

    /** @test */
    public function it_can_get_untranslated_menu()
    {
        $menu = $this->logFactory->menu(false);

        foreach ($menu as $date => $item) {
            $this->assertDate($date);

            // TODO: Complete the assertions
        }
    }

    /** @test */
    public function it_can_get_stats_table()
    {
        $this->assertTable($this->logFactory->statsTable());
    }

    /** @test */
    public function it_can_check_is_not_empty()
    {
        $this->assertFalse($this->logFactory->isEmpty());
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\LogViewer\Exceptions\LogNotFound
     */
    public function it_must_throw_a_filesystem_exception()
    {
        $this->logFactory->get('2222-11-11'); // Future FTW
    }
}
