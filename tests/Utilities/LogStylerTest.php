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
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Utilities\LogStyler */
    private $styler;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp()
    {
        parent::setUp();

        $this->styler = $this->app->make(\Arcanedev\LogViewer\Contracts\Utilities\LogStyler::class);
    }

    protected function tearDown()
    {
        unset($this->styler);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
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
        $icon = $this->styler->icon('danger', $default = 'fa fa-fw fa-danger');

        $this->assertSame('<i class="'.$default.'"></i>', $icon);
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
        $color = $this->styler->color('danger', $default = '#BADA55');

        $this->assertHexColor($color);
        $this->assertSame($default, $color);
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

    /** @test */
    public function it_can_get_string_to_highlight()
    {
        $expected = [
            '^#\d+',
            '^Stack trace:',
        ];

        $this->assertSame($expected, $this->styler->toHighlight());
    }
}
