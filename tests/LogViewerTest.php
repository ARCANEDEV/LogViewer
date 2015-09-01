<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\LogLevels;
use Arcanedev\LogViewer\LogViewer;
use Arcanedev\LogViewer\Utilities\Factory;
use Arcanedev\LogViewer\Utilities\Filesystem;

/**
 * Class LogViewerTest
 * @package Arcanedev\LogViewer\Tests
 */
class LogViewerTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogViewer */
    private $logViewer;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->logViewer = $this->app['log-viewer'];
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->logViewer);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(LogViewer::class,  $this->logViewer);
        $this->assertInstanceOf(Factory::class,    $this->logViewer->getFactory());
        $this->assertInstanceOf(Filesystem::class, $this->logViewer->getFilesystem());
        $this->assertInstanceOf(LogLevels::class,  $this->logViewer->getLogLevels());
    }

    /** @test */
    public function it_can_get_log_entries()
    {
        $date       = '2015-01-01';
        $logEntries = $this->logViewer->read($date);

        $this->assertCount(8, $logEntries);

        foreach ($logEntries as $logEntry) {
            $this->assertLogEntry($logEntry, $date);
        }
    }

    /** @test */
    public function it_can_get_log_entries_by_level()
    {
        $date       = '2015-01-01';
        foreach ($this->getLogLevels() as $level) {
            $logEntries = $this->logViewer->read($date, $level);

            $this->assertCount(1, $logEntries);

            foreach ($logEntries as $logEntry) {
                $this->assertLogEntry($logEntry, $date);
            }
        }
    }

    /** @test */
    public function it_can_delete_a_log_file()
    {
        $date = date('Y-m-d');

        $this->createDummyLog($date);

        // Assert log exists
        $logEntries = $this->logViewer->read($date);

        $this->assertNotEmpty($logEntries);

        // Assert log deletion
        try {
            $deleted = $this->logViewer->delete($date);
            $message = '';
        }
        catch (\Exception $e) {
            $deleted = false;
            $message = $e->getMessage();
        }

        $this->assertTrue($deleted, $message);
    }

    /** @test */
    public function it_can_get_log_files()
    {
        $logs = $this->logViewer->logs();

        $this->assertCount(2, $logs);

        foreach ($logs as $date) {
            $this->assertRegExp('/(\d){4}(-(\d){2}){2}/', $date);
        }
    }

    /** @test */
    public function it_can_get_all_levels()
    {
        $levels = $this->logViewer->levels();

        $this->assertCount(8, $levels);
        $this->assertEquals($this->getLogLevels(), $levels);
    }
}
