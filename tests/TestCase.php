<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\EntryCollection;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\LogViewerServiceProvider;
use Carbon\Carbon;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Class AbstractTestCase
 * @package Arcanedev\LogViewer\Tests
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Bench Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
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
     * @param  \Illuminate\Foundation\Application  $app
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
     * @param  \Illuminate\Foundation\Application  $app
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
     * Assert Log entries
     *
     * @param EntryCollection $entries
     * @param string          $date
     */
    protected function assertLogEntries(EntryCollection $entries, $date)
    {
        foreach ($entries as $entry) {
            $this->assertLogEntry($entry, $date);
        }
    }

    /**
     * Assert log entry
     *
     * @param  LogEntry $entry
     * @param  string  $date
     */
    protected function assertLogEntry(LogEntry $entry, $date)
    {
        $dt = Carbon::createFromFormat('Y-m-d', $date);

        $this->assertContains($entry->level, $this->getLogLevels());
        $this->assertNotEmpty($entry->header);
        $this->assertInstanceOf(Carbon::class, $entry->datetime);
        $this->assertTrue($entry->datetime->isSameDay($dt));
        $this->assertNotEmpty($entry->stack);
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

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get filesystem utility
     *
     * @return \Arcanedev\LogViewer\Utilities\Filesystem
     */
    protected function getFilesystem()
    {
        return $this->app['log-viewer.filesystem'];
    }

    /**
     * Get log levels
     *
     * @return array
     */
    public function getLogLevels()
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
        $fixtures    = __DIR__ . '/fixtures';
        $source      = $fixtures . '/dummy.log';
        $destination = $fixtures . "/logs/laravel-{$date}.log";

        return copy($source, $destination);
    }
}
