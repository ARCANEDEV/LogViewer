<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Helpers\LogParser;
use Arcanedev\LogViewer\LogViewerServiceProvider;
use Arcanedev\LogViewer\Providers\DeferredServicesProvider;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Orchestra\Testbench\TestCase as BaseTestCase;
use PHPUnit\Framework\Constraint\RegularExpression;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Class     TestCase
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    protected static array $logLevels = [];

    protected static array $locales   = [
        'ar', 'bg', 'de', 'en', 'es', 'et', 'fa', 'fr', 'hu', 'hy', 'id', 'it', 'ja', 'ko', 'ms', 'nl', 'pl',
        'pt-BR', 'ro', 'ru', 'si', 'sv', 'th', 'tr', 'uk', 'zh-TW', 'zh'
    ];

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$logLevels = static::getLogLevels();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$logLevels = [];
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            LogViewerServiceProvider::class,
            DeferredServicesProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['path.storage'] = realpath(__DIR__.'/fixtures');

        /** @var \Illuminate\Config\Repository $config */
        $config = $app['config'];

        $config->set('log-viewer.storage-path', $app['path.storage'].DIRECTORY_SEPARATOR.'logs');
    }

    /* -----------------------------------------------------------------
     |  Custom assertions
     | -----------------------------------------------------------------
     */

    /**
     * Asserts that a string is a valid JSON string.
     *
     * @param  \Illuminate\Contracts\Support\Jsonable|mixed  $object
     * @param  string                                        $message
     */
    public static function assertJsonObject($object, $message = ''): void
    {
        static::assertInstanceOf(Jsonable::class, $object);
        static::assertJson($object->toJson(JSON_PRETTY_PRINT), $message);

        static::assertInstanceOf('JsonSerializable', $object);
        static::assertJson(json_encode($object, JSON_PRETTY_PRINT), $message);
    }

    /**
     * Assert Log object.
     *
     * @param  \Arcanedev\LogViewer\Entities\Log  $log
     * @param  string                             $date
     */
    protected static function assertLog(Log $log, $date): void
    {
        static::assertEquals($date, $log->date);
        static::assertLogEntries($log->date, $log->entries());
    }

    /**
     * Assert Log entries object.
     *
     * @param  string                                            $date
     * @param  \Arcanedev\LogViewer\Entities\LogEntryCollection  $entries
     */
    protected static function assertLogEntries($date, LogEntryCollection $entries): void
    {
        foreach ($entries as $entry) {
            static::assertLogEntry($date, $entry);
        }
    }

    /**
     * Assert log entry object.
     *
     * @param  string                                  $date
     * @param  \Arcanedev\LogViewer\Entities\LogEntry  $entry
     */
    protected static function assertLogEntry($date, LogEntry $entry): void
    {
        $dt = Carbon::createFromFormat('Y-m-d', $date);

        static::assertInLogLevels($entry->level);
        static::assertInstanceOf(Carbon::class, $entry->datetime);
        static::assertTrue($entry->datetime->isSameDay($dt));
        static::assertNotEmpty($entry->header);
        static::assertNotEmpty($entry->stack);
    }

    /**
     * Assert in log levels.
     *
     * @param  string  $level
     * @param  string  $message
     */
    protected static function assertInLogLevels($level, $message = ''): void
    {
        static::assertContains($level, static::$logLevels, $message);
    }

    /**
     * Assert levels.
     *
     * @param  array  $levels
     */
    protected static function assertLevels(array $levels): void
    {
        static::assertCount(8, $levels);

        foreach (static::getLogLevels() as $key => $value) {
            static::assertArrayHasKey($key, $levels);
            static::assertEquals($value, $levels[$key]);
        }
    }

    /**
     * Assert translated level.
     *
     * @param  string  $locale
     * @param  array   $levels
     */
    protected function assertTranslatedLevels($locale, $levels): void
    {
        foreach ($levels as $level => $translatedLevel) {
            static::assertTranslatedLevel($locale, $level, $translatedLevel);
        }
    }

    /**
     * Assert translated level.
     *
     * @param  string  $locale
     * @param  string  $level
     * @param  string  $actualTrans
     */
    protected static function assertTranslatedLevel($locale, $level, $actualTrans): void
    {
        $expected = static::getTranslatedLevel($locale, $level);

        static::assertEquals($expected, $actualTrans);
    }

    /**
     * Assert dates.
     *
     * @param  array   $dates
     * @param  string  $message
     */
    public static function assertDates(array $dates, $message = ''): void
    {
        foreach ($dates as $date) {
            static::assertDate($date, $message);
        }
    }

    /**
     * Assert date [YYYY-MM-DD].
     *
     * @param  string  $date
     * @param  string  $message
     */
    public static function assertDate($date, $message = ''): void
    {
        static::assertMatchesRegExp('/'.LogParser::REGEX_DATE_PATTERN.'/', $date, $message);
    }

    /**
     * Assert Menu item.
     *
     * @param  array   $item
     * @param  string  $name
     * @param  int     $count
     * @param  bool    $withIcons
     */
    protected static function assertMenuItem($item, $name, $count, $withIcons = true): void
    {
        static::assertArrayHasKey('name', $item);
        static::assertEquals($name, $item['name']);
        static::assertArrayHasKey('count', $item);
        static::assertEquals($count, $item['count']);

        if ($withIcons) {
            static::assertArrayHasKey('icon', $item);
            static::assertStringStartsWith('fa fa-fw fa-', $item['icon']);
        }
        else {
            static::assertArrayNotHasKey('icon', $item);
        }
    }

    /**
     * Assert HEX Color.
     *
     * @param  string  $color
     * @param  string  $message
     */
    protected static function assertHexColor($color, $message = ''): void
    {
        $pattern = '/^#?([a-f0-9]{3}|[a-f0-9]{6})$/i';

        static::assertMatchesRegExp($pattern, $color, $message);
    }

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @todo Remove this method when phpunit 8 not used
     *
     * @param  string  $pattern
     * @param  string  $string
     * @param  string  $message
     *
     */
    public static function assertMatchesRegExp($pattern, $string, $message = ''): void
    {
        static::assertThat($string, new RegularExpression($pattern), $message);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get Illuminate Filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function illuminateFile()
    {
        return $this->app->make('files');
    }

    /**
     * Get Filesystem Utility instance.
     *
     * @return \Arcanedev\LogViewer\Utilities\Filesystem
     */
    protected function filesystem()
    {
        return $this->app->make(Filesystem::class);
    }

    /**
     * Get Translator Repository.
     *
     * @return \Illuminate\Translation\Translator
     */
    protected static function trans()
    {
        return app('translator');
    }

    /**
     * Get Config Repository.
     *
     * @return \Illuminate\Config\Repository
     */
    protected function config()
    {
        return $this->app->make('config');
    }

    /**
     * Get log path.
     *
     * @param  string  $date
     *
     * @return string
     */
    public function getLogPath($date)
    {
        return $this->filesystem()->path($date);
    }

    protected static function fixturePath(?string $path = null): string
    {
        return is_null($path)
            ? __DIR__.'/fixtures'
            : __DIR__.'/fixtures/'.$path;
    }

    /**
     * Get log content.
     *
     * @param  string  $date
     *
     * @return string
     */
    public function getLogContent($date)
    {
        return $this->filesystem()->read($date);
    }

    /**
     * Get logs dates.
     *
     * @return array
     */
    public function getDates()
    {
        return $this->filesystem()->dates();
    }

    /**
     * Get log object from fixture.
     *
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    protected function getLog($date)
    {
        $path = $this->getLogPath($date);
        $raw  = $this->getLogContent($date);

        return Log::make($date, $path, $raw);
    }

    /**
     * Get random entry from a log file.
     *
     * @param  string  $date
     *
     * @return mixed
     */
    protected function getRandomLogEntry($date)
    {
        return $this->getLog($date)
            ->entries()
            ->random(1)
            ->first();
    }

    /**
     * Get log levels.
     *
     * @return array
     */
    protected static function getLogLevels()
    {
        return static::$logLevels = (new ReflectionClass(LogLevel::class))
            ->getConstants();
    }

    /**
     * Create dummy log.
     *
     * @param string $date
     * @param string $path
     *
     * @return bool
     */
    protected static function createDummyLog(string $date, string $path): bool
    {
        return copy(
            static::fixturePath('dummy.log'), // Source
            "{$path}/laravel-{$date}.log"     // Destination
        );
    }

    /**
     * Get translated level.
     *
     * @param  string  $locale
     * @param  string  $key
     *
     * @return mixed
     */
    private static function getTranslatedLevel($locale, $key)
    {
        return Arr::get(static::getTranslatedLevels(), "$locale.$key");
    }

    /**
     * Get translated levels.
     *
     * @return array
     */
    protected static function getTranslatedLevels()
    {
        $levels = [
            'all'               => 'All',
            LogLevel::EMERGENCY => 'Emergency',
            LogLevel::ALERT     => 'Alert',
            LogLevel::CRITICAL  => 'Critical',
            LogLevel::ERROR     => 'Error',
            LogLevel::WARNING   => 'Warning',
            LogLevel::NOTICE    => 'Notice',
            LogLevel::INFO      => 'Info',
            LogLevel::DEBUG     => 'Debug',
        ];

        return array_map(function ($locale) use ($levels) {
            return array_map(function ($level) use ($locale) {
                return static::trans()->get($level, [], $locale);
            }, $levels);
        }, array_combine(static::$locales, static::$locales));
    }

    /**
     * Get config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return realpath(config_path());
    }
}
