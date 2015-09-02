<?php namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class LogEntryCollectionTest
 * @package Arcanedev\LogViewer\Tests\Entities
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
        $dates = [
            '2015-01-01',
            '2015-01-02',
        ];

        foreach ($dates as $date) {
            $raw     = $this->getLogContent($date);
            $entries = (new LogEntryCollection)->load($raw);
            $this->assertLogEntries($entries, $date);
            $this->assertCount(8, $entries);
        }
    }
}
