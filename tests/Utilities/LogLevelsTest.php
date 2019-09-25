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
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Utilities\LogLevels  */
    private $levels;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    public function setUp(): void
    {
        parent::setUp();

        $this->levels = $this->app->make(\Arcanedev\LogViewer\Contracts\Utilities\LogLevels::class);
    }

    public function tearDown(): void
    {
        unset($this->levels);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(LogLevels::class, $this->levels);
    }

    /** @test */
    public function it_can_get_all_levels()
    {
        static::assertLevels($this->levels->lists());
    }

    /** @test */
    public function it_can_get_all_levels_by_static_method()
    {
        static::assertLevels(LogLevels::all());
    }

    /** @test */
    public function it_can_get_all_translated_levels()
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            $levels = $this->levels->names($locale);

            static::assertTranslatedLevels($locale, $levels);
        }
    }

    /** @test */
    public function it_must_choose_the_log_viewer_locale_instead_of_app_locale()
    {
        static::assertNotEquals('auto', $this->levels->getLocale());
        static::assertSame($this->app->getLocale(), $this->levels->getLocale());

        $this->levels->setLocale('fr');

        static::assertSame('fr', $this->levels->getLocale());
        static::assertNotEquals($this->app->getLocale(), $this->levels->getLocale());
    }

    /** @test */
    public function it_can_translate_levels_automatically()
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            static::assertTranslatedLevels(
                $this->app->getLocale(),
                $this->levels->names()
            );

            static::assertTranslatedLevels(
                $locale,
                $this->levels->names($locale)
            );
        }
    }
}
