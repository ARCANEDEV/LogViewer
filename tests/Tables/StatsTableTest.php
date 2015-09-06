<?php namespace Arcanedev\LogViewer\Tests\Tables;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Tables\StatsTable;

/**
 * Class     StatsTableTest
 *
 * @package  Arcanedev\LogViewer\Tests\Tables
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class StatsTableTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var StatsTable */
    private $table;

    /** @var array */
    private $rawData;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->table   = new StatsTable(
            $this->rawData = $this->app['arcanedev.log-viewer']->stats(),
            $this->app['arcanedev.log-viewer.levels']
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->table);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(StatsTable::class, $this->table);
    }

    /** @test */
    public function it_can_make_instance()
    {
        $this->table = StatsTable::make(
            $this->app['arcanedev.log-viewer']->stats(),
            $this->app['arcanedev.log-viewer.levels']
        );

        $this->assertTable($this->table);
    }

    /** @test */
    public function it_can_get_header()
    {
        $this->assertTableHeader($this->table);
    }

    /** @test */
    public function it_can_get_rows()
    {
        $this->assertTableRows($this->table);
    }

    /** @test */
    public function it_can_get_footer()
    {
        $this->assertTableFooter($this->table);
    }

    /** @test */
    public function it_can_get_raw_data()
    {
        $this->assertEquals(
            $this->rawData,
            $this->table->data()
        );
    }

    /** @test */
    public function it_can_get_json_data_for_chart()
    {
        $json = $this->table->totalsJson();

        $this->assertJson($json);
    }
}
