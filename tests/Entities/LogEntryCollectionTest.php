<?php namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogEntryCollectionTest
 *
 * @package  Arcanedev\LogViewer\Tests\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryCollectionTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogEntryCollection */
    private $entries;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->entries = new LogEntryCollection;
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->entries);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(LogEntryCollection::class, $this->entries);
        $this->assertCount(0, $this->entries);
    }

    /** @test */
    public function it_can_load_raw_entries()
    {
        foreach ($this->getDates() as $date) {
            $entries = $this->getEntries($date);

            $this->assertLogEntries($date, $entries);
            $this->assertCount(8, $entries);
        }
    }

    /** @test */
    public function it_can_get_entries_by_level()
    {
        foreach ($this->getDates() as $date) {
            $this->entries = $this->getEntries($date);

            foreach (self::$logLevels as $level) {
                $entry = $this->entries->filterByLevel($level);

                $this->assertLogEntries($date, $entry);
            }
        }
    }

    /** @test */
    public function it_can_get_stats()
    {
        foreach ($this->getDates() as $date) {
            $this->entries = $this->getEntries($date);

            $stats = $this->entries->stats();

            foreach ($stats as $level => $count) {
                $this->assertEquals(($level === 'all') ? 8 : 1, $count);
            }
        }
    }

    /** @test */
    public function it_can_get_tree()
    {
        foreach ($this->getDates() as $date) {
            $this->entries = $this->getEntries($date);

            $tree = $this->entries->tree();

            $this->assertCount(9, $tree);

            foreach ($tree as $level => $item) {
                $this->assertArrayHasKey('name', $item);
                $this->assertArrayHasKey('count', $item);

                $this->assertEquals($level, $item['name']);
                $this->assertEquals(($level === 'all') ? 8 : 1, $item['count']);
            }
        }
    }

    /* ------------------------------------------------------------------------------------------------
     |  Private functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get log entries
     *
     * @param  string  $date
     *
     * @return LogEntryCollection
     */
    private function getEntries($date)
    {
        $raw     = $this->getLogContent($date);

        return (new LogEntryCollection)->load($raw);
    }
}
