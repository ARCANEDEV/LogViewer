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
        $this->log  = $this->getLog('2015-01-01');

        $this->assertInstanceOf(Log::class, $this->log);
    }

    /** @test */
    public function it_can_get_all_log_entries()
    {
        $date       = '2015-01-01';
        $this->log  = $this->getLog($date);
        $logEntries = $this->log->entries();

        $this->assertCount(8, $logEntries);

        foreach ($logEntries as $entry) {
            $this->assertLogEntry($entry, $date);
        }
    }

    /** @test */
    public function it_can_filter_log_entries_by_level()
    {
        $date       = '2015-01-01';

        foreach ($this->getLogLevels() as $level) {
            $this->log  = $this->getLog($date, $level);
            $logEntries = $this->log->entries();

            $this->assertCount(1, $logEntries);
            foreach ($logEntries as $entry) {
                $this->assertLogEntry($entry, $date);
            }
        }
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function it_must_throw_a_filesystem_exception()
    {
        $this->getLog('2961-01-01'); // Planet Express (Futurama)
    }
}
