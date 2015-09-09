<?php namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\LogMenu;

/**
 * Class     LogMenuTest
 *
 * @package  Arcanedev\LogViewer\Tests\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogMenuTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogMenu */
    private $menu;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->menu = $this->app['arcanedev.log-viewer.menu'];
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->menu);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(
            'Arcanedev\\LogViewer\\Utilities\\LogMenu',
            $this->menu
        );
    }

    /** @test */
    public function it_can_make_menu_with_helper()
    {
        $log  = $this->getLog('2015-01-01');

        $menu = log_menu()->make($log);
        // TODO: complete the assertion
    }
}
