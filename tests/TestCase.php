<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\LogViewerServiceProvider;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Foundation\Application;
use JsonSerializable;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Class AbstractTestCase
 * @package Arcanedev\LogViewer\Tests
 */
abstract class TestCase extends BaseTestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var array */
    protected static $logLevels;

    protected static $locales = ['ar', 'en', 'fr'];

    /* ------------------------------------------------------------------------------------------------
     |  Main functions
     | ------------------------------------------------------------------------------------------------
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$logLevels = self::getLogLevels();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        static::$logLevels = [];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Bench Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get package providers.
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LogViewerServiceProvider::class
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['path.storage'] = __DIR__ . '/fixtures';
    }

    /* ------------------------------------------------------------------------------------------------
     |  Custom assertions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Asserts that a string is a valid JSON string.
     *
     * @param  Jsonable|mixed  $object
     * @param  string          $message
     */
    public static function assertJson($object, $message = '')
    {
        self::assertInstanceOf(Jsonable::class, $object);
        parent::assertJson($object->toJson(JSON_PRETTY_PRINT), $message);

        self::assertInstanceOf(JsonSerializable::class, $object);
        parent::assertJson(json_encode($object, JSON_PRETTY_PRINT), $message);
    }

    /**
     * Assert Log object
     *
     * @param  Log     $log
     * @param  string  $date
     */
    protected function assertLog(Log $log, $date)
    {
        $this->assertEquals($date, $log->date);
        $this->assertLogEntries($log->entries(), $log->date);
    }

    /**
     * Assert Log entries object
     *
     * @param  LogEntryCollection  $entries
     * @param  string              $date
     */
    protected function assertLogEntries(LogEntryCollection $entries, $date)
    {
        foreach ($entries as $entry) {
            $this->assertLogEntry($entry, $date);
        }
    }

    /**
     * Assert log entry object
     *
     * @param  LogEntry  $entry
     * @param  string    $date
     */
    protected function assertLogEntry(LogEntry $entry, $date)
    {
        $dt = Carbon::createFromFormat('Y-m-d', $date);

        $this->assertInLogLevels($entry->level);
        $this->assertInstanceOf(Carbon::class, $entry->datetime);
        $this->assertTrue($entry->datetime->isSameDay($dt));
        $this->assertNotEmpty($entry->header);
        $this->assertNotEmpty($entry->stack);
    }

    /**
     * Assert in log levels
     *
     * @param  string  $level
     * @param  string  $message
     */
    protected function assertInLogLevels($level, $message = '')
    {
        $this->assertContains($level, self::$logLevels, $message);
    }

    /**
     * Assert levels
     *
     * @param  array  $levels
     */
    protected function assertLevels(array $levels)
    {
        $this->assertCount(8, $levels);

        foreach ($this->getLogLevels() as $key => $value) {
            $this->assertArrayHasKey($key, $levels);
            $this->assertEquals($value, $levels[$key]);
        }
    }

    /**
     * Assert translated level
     *
     * @param  string  $locale
     * @param  array   $levels
     */
    protected function assertTranslatedLevels($locale, $levels)
    {
        foreach ($levels as $level => $translatedLevel) {
            $this->assertTranslatedLevel($locale, $level, $translatedLevel);
        }
    }

    /**
     * Assert translated level
     *
     * @param  string  $locale
     * @param  string  $key
     * @param  string  $translatedLevel
     */
    protected function assertTranslatedLevel($locale, $key, $translatedLevel)
    {
        $this->assertEquals(
            $this->getTranslatedLevel($locale, $key),
            $translatedLevel
        );
    }

    /**
     * Assert dates
     *
     * @param  array   $dates
     * @param  string  $message
     */
    public function assertDates(array $dates, $message = '')
    {
        foreach ($dates as $date) {
            $this->assertDate($date, $message);
        }
    }

    /**
     * Assert date [yyyy-mm-dd]
     *
     * @param  string  $date
     * @param  string  $message
     */
    public function assertDate($date, $message = '')
    {
        $this->assertRegExp('/' . REGEX_DATE_PATTERN . '/', $date, $message);
    }

    /**
     * Assert Menu item.
     *
     * @param  array      $item
     * @param  string     $name
     * @param  int        $count
     * @param  bool|true  $withIcons
     */
    protected function assertMenuItem($item, $name, $count, $withIcons = true)
    {
        $this->assertArrayHasKey('name', $item);
        $this->assertEquals($name, $item['name']);
        $this->assertArrayHasKey('count', $item);
        $this->assertEquals($count, $item['count']);

        if ($withIcons) {
            $this->assertArrayHasKey('icon', $item);
            $this->assertStringStartsWith('fa fa-fw fa-', $item['icon']);
        }
        else {
            $this->assertArrayNotHasKey('icon', $item);
        }
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get filesystem utility
     *
     * @return \Arcanedev\LogViewer\Utilities\Filesystem
     */
    protected function filesystem()
    {
        return $this->app['log-viewer.filesystem'];
    }

    /**
     * Get translator repository
     *
     * @return \Illuminate\Translation\Translator
     */
    protected function trans()
    {
        return $this->app['translator'];
    }

    /**
     * Get config repository
     *
     * @return \Illuminate\Config\Repository
     */
    protected function config()
    {
        return $this->app['config'];
    }

    /**
     * Get log path
     *
     * @param  string $date
     *
     * @return string
     */
    public function getLogPath($date)
    {
        return $this->filesystem()->path($date);
    }

    /**
     * Get log content
     *
     * @param  string  $date
     *
     * @return string
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function getLogContent($date)
    {
        return $this->filesystem()->read($date);
    }

    /**
     * Get log object from fixture
     *
     * @param  string  $date
     *
     * @return Log
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
            ->random(1);
    }

    /**
     * Get log levels.
     *
     * @return array
     */
    protected static function getLogLevels()
    {
        $class = new ReflectionClass(new LogLevel);

        return self::$logLevels = $class->getConstants();
    }

    /**
     * Create dummy log.
     *
     * @param  string  $date
     *
     * @return bool
     */
    protected function createDummyLog($date)
    {
        return copy(
            storage_path('dummy.log'),                // Source
            storage_path("/logs/laravel-{$date}.log") // Destination
        );
    }

    /**
     * Get dummy menu items.
     *
     * @param  bool|false  $icon
     *
     * @return array
     */
    protected function getDummyMenuItems($icon = false)
    {
        $items  = [];
        $config = $this->config();

        foreach (array_values(self::getLogLevels()) as $level) {
            $item['name']  = $level;
            $item['count'] = rand(0, 50);

            if ($icon) {
                $item['icon'] = $config->get('log-viewer.menu.icons.' . $level);
            }

            $items[] = [$level, $item];
        }

        return $items;
    }

    /**
     * Get translated level.
     *
     * @param  string  $locale
     * @param  string  $key
     *
     * @return mixed
     */
    private function getTranslatedLevel($locale, $key)
    {
        return array_get(
            $this->getTranslatedLevels(),
            "$locale.$key"
        );
    }

    /**
     * Get translated levels.
     *
     * @return array
     */
    protected function getTranslatedLevels()
    {
        $translator = $this->app['translator'];

        return array_map(function ($locale) use ($translator) {
            return $translator->get('log-viewer::levels', [], $locale);
        }, array_combine(self::$locales, self::$locales));
    }
}
