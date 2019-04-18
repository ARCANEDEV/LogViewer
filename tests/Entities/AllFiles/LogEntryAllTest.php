<?php namespace Arcanedev\LogViewer\Tests\Entities\AllFiles;

use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogEntryTest
 *
 * @package  Arcanedev\LogViewer\Tests\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryAllTest extends TestCase
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
        $this->app['config']->set('log-viewer.parse-all-files-in-log-path', true);

        $filename ='laravel-2015-01.log';
        $this->entry = $this->getRandomLogEntry(config('log-viewer.storage-path'). DS. $filename );
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

        $filename ='laravel-2015-01.log';
        static::assertLogEntry($filename , $this->entry);
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
