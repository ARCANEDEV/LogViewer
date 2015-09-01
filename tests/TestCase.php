<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\LogViewerServiceProvider;
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
     * Assert log entry
     *
     * @param  array   $entry
     * @param  string  $date
     */
    protected function assertLogEntry(array $entry, $date)
    {
        $this->assertArrayHasKey('level', $entry);
        $this->assertContains($entry['level'], $this->getLogLevels());

        $this->assertArrayHasKey('header', $entry);
        $this->assertNotEmpty($entry['header']);
        $this->assertStringStartsWith('[' . $date, $entry['header']);

        $this->assertArrayHasKey('stack', $entry);
        $this->assertNotEmpty($entry['stack']);
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
     * Get a log entries
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return Log
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    protected function getLog($date, $level = 'all')
    {
        $raw = $this->getFilesystem()->read($date);

        return new Log($raw, $this->getLogLevels(), $level);
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
