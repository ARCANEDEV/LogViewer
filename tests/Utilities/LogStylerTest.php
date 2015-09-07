<?php namespace Arcanedev\LogViewer\Tests\Utilities;
use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\LogStyler;

/**
 * Class     LogStylerTest
 *
 * @package  Arcanedev\LogViewer\Tests\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogStylerTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogStyler */
    private $styler;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->styler = $this->app['arcanedev.log-viewer.styler'];
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->styler);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_ben_instantiated()
    {
        $this->assertInstanceOf(LogStyler::class, $this->styler);
    }

    /** @test */
    public function it_can_get_icon()
    {
        foreach (self::$logLevels as $level) {
            $this->assertRegExp(
                '/^<i class="fa fa-fw fa-(.*)"><\/i>$/',
                $this->styler->icon($level)
            );
        }
    }

    /** @test */
    public function it_can_get_default_when_icon_not_found()
    {
        $default = 'fa fa-fw fa-danger';
        $icon    = $this->styler->icon('danger', $default);

        $this->assertRegExp('/^<i class="fa fa-fw fa-(.*)"><\/i>$/', $icon);
        $this->assertEquals('<i class="' . $default . '"></i>', $icon);
    }

    /** @test */
    public function it_can_get_color()
    {
        foreach (self::$logLevels as $level) {
            $this->assertHexColor($this->styler->color($level));
        }
    }

    /** @test */
    public function it_can_get_default_when_color_not_found()
    {
        $default = '#BADA55'; // Bad ass color
        $color   = $this->styler->color('danger', $default);
        $this->assertHexColor($color);
        $this->assertEquals($default, $color);
    }

    /** @test */
    public function it_can_use_helper_to_get_icon()
    {
        foreach (self::$logLevels as $level) {
            $this->assertRegExp(
                '/^<i class="fa fa-fw fa-(.*)"><\/i>$/',
                log_styler()->icon($level)
            );
        }
    }

    /** @test */
    public function it_can_use_helper_get_color()
    {
        foreach (self::$logLevels as $level) {
            $this->assertHexColor(log_styler()->color($level));
        }
    }
}
