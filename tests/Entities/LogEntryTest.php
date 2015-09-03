<?php namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class LogEntryTest
 * @package Arcanedev\LogViewer\Tests\Entities
 */
class LogEntryTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogEntry */
    private $entry;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->entry = $this->getRandomLogEntry('2015-01-01');
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(LogEntry::class, $this->entry);
        $this->assertLogEntry($this->entry, '2015-01-01');
    }

    /** @test */
    public function it_can_convert_to_json()
    {
        $this->assertJson($this->entry);
    }
}
