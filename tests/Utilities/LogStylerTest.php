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
            $icon = $this->styler->icon($level);

            $this->assertRegExp('/^<i class="fa fa-fw fa-(.*)"><\/i>$/', $icon);
        }
    }

    /** @test */
    public function it_can_get_color()
    {
        foreach (self::$logLevels as $level) {
            $this->assertHexColor(
                $this->styler->color($level)
            );
        }
    }

    /**
     * Assert HEX Color
     *
     * @param  string  $color
     */
    protected function assertHexColor($color)
    {
        $this->assertRegExp('/^#?([a-f0-9]{3}|[a-f0-9]{6})$/i', $color);
    }
}
