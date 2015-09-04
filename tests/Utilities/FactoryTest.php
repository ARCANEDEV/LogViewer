<?php namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\Factory;
use Arcanedev\LogViewer\Utilities\Filesystem;

/**
 * Class FactoryTest
 * @package Arcanedev\LogViewer\Tests\Utilities
 */
class FactoryTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var Factory */
    private $logFactory;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->logFactory = $this->app['log-viewer.factory'];
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->logFactory);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Factory::class, $this->logFactory);
    }

    /** @test */
    public function it_can_get_filesystem_object()
    {
        $this->assertInstanceOf(
            Filesystem::class,
            $this->logFactory->getFilesystem()
        );
    }

    /** @test */
    public function it_can_get_log_entries()
    {
        $date       = '2015-01-01';
        $logEntries = $this->logFactory->entries($date);

        foreach ($logEntries as $logEntry) {
            $this->assertLogEntry($date, $logEntry);
        }
    }

    /** @test */
    public function it_can_get_dates()
    {
        $dates = $this->logFactory->dates();

        $this->assertCount(2, $dates);
        $this->assertDates($dates);
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        $logs = $this->logFactory->all();

        $this->assertInstanceOf(LogCollection::class, $logs);
        $this->assertCount(2, $logs);
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\LogViewer\Exceptions\LogNotFound
     */
    public function it_must_throw_a_filesystem_exception()
    {
        $this->logFactory->get('2222-11-11'); // Future FTW
    }
}
