<?php namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class LogTest
 * @package Arcanedev\LogViewer\Tests\Entities
 */
class LogTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var Log */
    private $log;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->log = $this->getLog('2015-01-01');
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->log);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $entries = $this->log->entries();

        $this->assertInstanceOf(Log::class, $this->log);
        $this->assertDate($this->log->date);
        $this->assertCount(8, $entries);
        $this->assertLogEntries($entries, $this->log->date);
    }

    /** @test */
    public function it_can_get_date()
    {
        $dates = [
            '2015-01-01',
            '2015-01-02',
        ];

        foreach ($dates as $date) {
            $log = $this->getLog($date);

            $this->assertDate($log->date);
            $this->assertEquals($date, $log->date);
        }
    }

    /** @test */
    public function it_can_get_path()
    {
        $dates = [
            '2015-01-01',
            '2015-01-02',
        ];

        foreach ($dates as $date) {
            $log = $this->getLog($date);

            $this->assertFileExists($log->getPath());
        }
    }

    /** @test */
    public function it_can_get_raw_content()
    {
        $this->assertNotEmpty($this->log->getRaw());
        $this->assertEquals(
            $this->getLog($this->log->date)->getRaw(),
            $this->log->getRaw()
        );
    }

    /** @test */
    public function it_can_get_all_entries()
    {
        $dates = [
            '2015-01-01',
            '2015-01-02',
        ];

        foreach ($dates as $date) {
            $entries = $this->getLog($date)->entries();

            $this->assertCount(8, $entries);
            $this->assertLogEntries($entries, $date);
        }
    }

    /** @test */
    public function it_can_get_all_entries_by_level()
    {
        $dates = [
            '2015-01-01',
            '2015-01-02',
        ];

        foreach ($dates as $date) {
            $log = $this->getLog($date);

            foreach ($this->getLogLevels() as $level) {
                $this->assertCount(1, $log->entries($level));
                $this->assertLogEntries($log->entries(), $date);
            }
        }
    }

    /** @test */
    public function it_can_get_translated_menu()
    {
        $dates   = [
            '2015-01-01',
            '2015-01-02',
        ];

        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            foreach ($dates as $date) {
                $menu = $this->getLog($date)->menu();

                $this->assertCount(8, $menu);

                foreach ($menu as $level => $menuItem) {
                    $this->assertInLogLevels($level);
                    $this->assertTranslatedLevel($locale, $level, $menuItem['name']);
                    $this->assertEquals(1, $menuItem['count']);
                }
            }
        }
    }

    /** @test */
    public function it_can_get_tree()
    {
        $dates   = [
            '2015-01-01',
            '2015-01-02',
        ];

        foreach ($dates as $date) {
            $menu = $this->getLog($date)->tree();

            $this->assertCount(8, $menu);

            foreach ($menu as $level => $menuItem) {
                $this->assertInLogLevels($level);
                $this->assertInLogLevels($menuItem['name']);
                $this->assertEquals(1, $menuItem['count']);
            }
        }
    }
}
