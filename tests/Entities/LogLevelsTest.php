<?php namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\LogLevels;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class LogLevelsTest
 * @package Arcanedev\LogViewer\Tests\Entities
 */
class LogLevelsTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogLevels */
    private $logLevels;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->logLevels = new LogLevels;
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->logLevels);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(LogLevels::class, $this->logLevels);
    }

    /** @test */
    public function it_can_get_all_levels()
    {
        $this->assertLevels($this->logLevels->lists());
    }

    /** @test */
    public function it_can_get_all_levels_by_static_method()
    {
        $this->assertLevels(LogLevels::all());
    }

    /* ------------------------------------------------------------------------------------------------
     |  Custom assertion s
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Assert levels
     *
     * @param  array  $levels
     */
    private function assertLevels(array $levels)
    {
        $this->assertCount(8, $levels);

        foreach ($this->getLogLevels() as $key => $value) {
            $this->assertArrayHasKey($key, $levels);
            $this->assertEquals($value, $levels[$key]);
        }
    }
}
