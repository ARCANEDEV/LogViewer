<?php namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogCollectionTest
 *
 * @package  Arcanedev\LogViewer\Tests\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogCollectionTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogCollection */
    private $logs;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->logs = LogCollection::make();
    }

    public function tearDown()
    {
        unset($this->logs);

        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(LogCollection::class, $this->logs);
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        $this->assertCount(2,   $this->logs);
        $this->assertEquals(2,  $this->logs->count());
        $this->assertEquals(16, $this->logs->total());

        foreach ($this->logs as $date => $log) {
            $this->assertLog($log, $date);
            $this->assertCount(8,  $log->entries());
            $this->assertEquals(8, $log->entries()->count());
        }
    }

    /** @test */
    public function it_can_get_a_log_by_date()
    {
        $date = '2015-01-01';
        $log  = $this->logs->get($date);

        $this->assertLog($log, $date);
        $this->assertCount(8, $log->entries());
        $this->assertEquals(8, $log->entries()->count());
    }

    /** @test */
    public function it_can_get_the_log_entries_by_date()
    {
        $date    = '2015-01-01';
        $entries = $this->logs->entries($date);

        $this->assertLogEntries($date, $entries);
        $this->assertCount(8, $entries);
        $this->assertEquals(8, $entries->count());
    }

    /** @test */
    public function it_can_get_the_log_entries_by_date_and_level()
    {
        $date    = '2015-01-01';

        foreach (self::$logLevels as $level) {
            $entries = $this->logs->entries($date, $level);

            $this->assertLogEntries($date, $entries);
            $this->assertCount(1, $entries);
            $this->assertEquals(1, $entries->count());
        }

        $entries = $this->logs->entries($date, 'all');

        $this->assertLogEntries($date, $entries);
        $this->assertCount(8, $entries);
        $this->assertEquals(8, $entries->count());
    }

    /** @test */
    public function it_can_get_logs_dates()
    {
        foreach ($this->getDates() as $date) {
            $this->assertContains($date, $this->logs->dates());
        }
    }

    /** @test */
    public function it_can_get_logs_stats()
    {
        $stats = $this->logs->stats();

        foreach ($stats as $date => $counters) {
            $this->assertDate($date);

            foreach ($counters as $level => $counter) {
                if ($level === 'all') {
                    $this->assertEquals(8, $counter);

                    continue;
                }

                $this->assertEquals(1, $counter);
            }
        }
    }

    /** @test */
    public function it_can_get_log_tree()
    {
        $tree = $this->logs->tree();

        $this->assertCount(2, $tree);

        foreach ($tree as $date => $levels) {
            $this->assertDate($date);

            foreach ($levels as $level => $item) {
                $this->assertEquals($level, $item['name']);
                $this->assertEquals($level === 'all' ? 8 : 1, $item['count']);
            }
        }
    }

    /** @test */
    public function it_can_get_log_menu()
    {
        foreach(self::$locales as $locale) {
            $this->app->setLocale($locale);
            $menu = $this->logs->menu();

            foreach ($menu as $date => $levels) {
                $this->assertDate($date);

                foreach ($levels as $level => $item) {
                    $this->assertNotEquals($level, $item['name']);
                    $this->assertTranslatedLevel($locale, $level, $item['name']);
                    $this->assertEquals($level == 'all' ? 8 : 1, $item['count']);
                }
            }
        }
    }

    /**
     * @test
     *
     * @expectedException        \Arcanedev\LogViewer\Exceptions\LogNotFoundException
     * @expectedExceptionMessage Log not found in this date [2222-01-01]
     */
    public function it_must_throw_a_log_not_found_on_get_method()
    {
        $this->logs->get('2222-01-01');
    }

    /**
     * @test
     *
     * @expectedException        \Arcanedev\LogViewer\Exceptions\LogNotFoundException
     * @expectedExceptionMessage Log not found in this date [2222-01-01]
     */
    public function it_must_throw_a_log_not_found_on_log_method()
    {
        $this->logs->log('2222-01-01');
    }
}
