<?php namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class LogCollectionTest
 * @package Arcanedev\LogViewer\Tests\Entities
 */
class LogCollectionTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogCollection */
    private $logs;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->logs = new LogCollection;
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->logs);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(LogCollection::class, $this->logs);
        $this->assertEquals(2, $this->logs->count());
        $this->assertEquals(16, $this->logs->total());
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        $logs = $this->logs->all();

        $this->assertCount(2, $logs);

        foreach ($logs as $date => $log) {
            /** @var Log $log */
            $this->assertLog($log, $date);
            $this->assertCount(8, $log->entries());
        }
    }
}
