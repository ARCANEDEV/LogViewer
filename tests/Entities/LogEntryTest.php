<?php namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogEntryTest
 *
 * @package  Arcanedev\LogViewer\Tests\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Entities\LogEntry */
    private $entry;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp()
    {
        parent::setUp();

        $this->entry = $this->getRandomLogEntry('2015-01-01');
    }

    protected function tearDown()
    {
        unset($this->entry);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(LogEntry::class, $this->entry);
        static::assertLogEntry('2015-01-01', $this->entry);
    }

    /** @test */
    public function it_can_convert_to_json()
    {
        static::assertJsonObject($this->entry);
    }

    /** @test */
    public function it_can_check_if_same_level()
    {
        $level = $this->entry->level;

        static::assertTrue($this->entry->isSameLevel($level));
    }

    /** @test */
    public function it_can_get_stack()
    {
        static::assertNotSame($this->entry->stack, $this->entry->stack());
    }
}
