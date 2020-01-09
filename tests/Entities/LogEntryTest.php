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

    protected function setUp(): void
    {
        parent::setUp();

        $this->entry = $this->getRandomLogEntry('2015-01-01');
    }

    protected function tearDown(): void
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
        static::assertFalse($this->entry->hasContext());
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

    /** @test */
    public function it_can_extract_context()
    {
        $entry = new LogEntry(
            'SUCCESS',
            '[2020-01-09 10:27:00] production.SUCCESS: New user registered {"id":1,"name":"John DOE"}'
        );

        static::assertTrue($entry->hasContext());
        static::assertSame('New user registered', $entry->header);

        $expected = ['id' => 1, 'name' => 'John DOE'];

        static::assertEquals($expected, $entry->context);
        static::assertSame(
            json_encode($expected, JSON_PRETTY_PRINT),
            $entry->context()
        );
    }
}
