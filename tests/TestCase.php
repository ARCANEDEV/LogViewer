<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\LogViewerServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Class AbstractTestCase
 * @package Arcanedev\LogViewer\Tests
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var array */
    protected static $logLevels;

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
     * Assert translated level
     *
     * @param  string  $locate
     * @param  string  $key
     * @param  string  $translatedLevel
     */
    protected function assertTranslatedLevel($locate, $key, $translatedLevel)
    {
        $this->assertEquals(
            $this->getTranslatedLevel($locate, $key),
            $translatedLevel
        );
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
        return new Log($date, $this->getLogContent($date));
    }

    /**
     * Get random entry from a log file
     *
     * @param  string  $date
     *
     * @return mixed
     */
    protected function getRandomLogEntry($date)
    {
        return $this->getLog($date)->entries()->random(1);
    }

    /**
     * Get log levels
     *
     * @return array
     */
    public static function getLogLevels()
    {
        $class = new ReflectionClass(new LogLevel);

        return $class->getConstants();
    }

    /**
     * Create dummy log
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
     * Get translated level
     *
     * @param  string  $locale
     * @param  string  $key
     *
     * @return mixed
     */
    private function getTranslatedLevel($locale, $key)
    {
        return array_get($this->getTranslatedLevels(), "$locale.$key");
    }

    /**
     * Get translated levels
     *
     * @return array
     */
    protected function getTranslatedLevels()
    {
        $levels = $this->getLogLevels();
        $trans  = [
            'en'  => [
                'Emergency', 'Alert', 'Critical', 'Error', 'Warning', 'Notice', 'Info', 'Debug',
            ],
            'fr'  => [
                'Urgence', 'Alerte', 'Critique', 'Erreur', 'Avertissement', 'Notice', 'Info', 'Debug',
            ]
        ];

        return array_map(function ($items) use ($levels) {
            return array_combine($levels, $items);
        }, $trans);
    }
}
