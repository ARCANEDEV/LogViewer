<?php namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Utilities\LogLevels;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogLevelsTest
 *
 * @package  Arcanedev\LogViewer\Tests\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogLevelsTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogLevels */
    private $levels;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->levels = $this->app['arcanedev.log-viewer.levels'];
    }

    public function tearDown()
    {
        unset($this->levels);

        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(
            'Arcanedev\\LogViewer\\Utilities\\LogLevels',
            $this->levels
        );
    }

    /** @test */
    public function it_can_get_all_levels()
    {
        $this->assertLevels($this->levels->lists());
    }

    /** @test */
    public function it_can_get_all_levels_by_static_method()
    {
        $this->assertLevels(LogLevels::all());
    }

    /** @test */
    public function it_can_get_all_translated_levels()
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            $levels = $this->levels->names($locale);

            $this->assertTranslatedLevels($locale, $levels);
        }
    }

    /** @test */
    public function it_can_translate_levels_automatically()
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            $this->assertTranslatedLevels(
                $this->app->getLocale(),
                $this->levels->names()
            );

            $this->assertTranslatedLevels(
                $this->app->getLocale(),
                $this->levels->names('auto')
            );
        }
    }
}
