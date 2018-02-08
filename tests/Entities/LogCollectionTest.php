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
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Entities\LogCollection */
    private $logs;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp()
    {
        parent::setUp();

        $this->logs = LogCollection::make();
    }

    protected function tearDown()
    {
        unset($this->logs);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(LogCollection::class, $this->logs);
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        static::assertCount(2,   $this->logs);
        static::assertSame(2,  $this->logs->count());
        static::assertSame(16, $this->logs->total());

        foreach ($this->logs as $date => $log) {
            static::assertLog($log, $date);
            static::assertCount(8,  $log->entries());
            static::assertSame(8, $log->entries()->count());
        }
    }

    /** @test */
    public function it_can_get_a_log_by_date()
    {
        $log = $this->logs->get($date = '2015-01-01');

        static::assertLog($log, $date);
        static::assertCount(8, $log->entries());
        static::assertSame(8, $log->entries()->count());
    }

    /** @test */
    public function it_can_get_the_log_entries_by_date()
    {
        $entries = $this->logs->entries($date = '2015-01-01');

        static::assertLogEntries($date, $entries);
        static::assertCount(8, $entries);
        static::assertSame(8, $entries->count());
    }

    /** @test */
    public function it_can_get_the_log_entries_by_date_and_level()
    {
        $date = '2015-01-01';

        foreach (self::$logLevels as $level) {
            $entries = $this->logs->entries($date, $level);

            static::assertLogEntries($date, $entries);
            static::assertCount(1, $entries);
            static::assertSame(1, $entries->count());
        }

        $entries = $this->logs->entries($date, 'all');

        static::assertLogEntries($date, $entries);
        static::assertCount(8, $entries);
        static::assertSame(8, $entries->count());
    }

    /** @test */
    public function it_can_get_logs_dates()
    {
        foreach ($this->getDates() as $date) {
            static::assertContains($date, $this->logs->dates());
        }
    }

    /** @test */
    public function it_can_get_logs_stats()
    {
        $stats = $this->logs->stats();

        foreach ($stats as $date => $counters) {
            static::assertDate($date);

            foreach ($counters as $level => $counter) {
                if ($level === 'all') {
                    static::assertSame(8, $counter);
                }
                else {
                    static::assertEquals(1, $counter);
                }
            }
        }
    }

    /** @test */
    public function it_can_get_log_tree()
    {
        $tree = $this->logs->tree();

        static::assertCount(2, $tree);

        foreach ($tree as $date => $levels) {
            static::assertDate($date);

            foreach ($levels as $level => $item) {
                static::assertEquals($level, $item['name']);
                static::assertSame($level === 'all' ? 8 : 1, $item['count']);
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
                static::assertDate($date);

                foreach ($levels as $level => $item) {
                    static::assertNotEquals($level, $item['name']);
                    static::assertTranslatedLevel($locale, $level, $item['name']);
                    static::assertSame($level == 'all' ? 8 : 1, $item['count']);
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
