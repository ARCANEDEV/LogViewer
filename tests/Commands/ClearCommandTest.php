<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests\Commands;

use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     ClearCommandTest
 *
 * @package  Arcanedev\LogViewer\Tests\Commands
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class ClearCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\LogViewer */
    private $logViewer;

    /** @var  string */
    private $path;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->logViewer = $this->app->make(\Arcanedev\LogViewer\Contracts\LogViewer::class);
        $this->path      = storage_path('logs-to-clear');

        $this->setupForTests();
    }

    protected function tearDown(): void
    {
        rmdir($this->path);
        unset($this->path);
        unset($this->logViewer);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
    |  Tests
    | -----------------------------------------------------------------
    */

    /** @test */
    public function it_can_delete_all_log_files(): void
    {
        static::assertEquals(0, $this->logViewer->count());

        static::createDummyLog(date('Y-m-d'), 'logs-to-clear');

        static::assertEquals(1, $this->logViewer->count());

        $this->artisan('log-viewer:clear')
             ->expectsQuestion('This will delete all the log files, Do you wish to continue?', 'yes')
             ->expectsOutput('Successfully cleared the logs!')
             ->assertExitCode(0);

        static::assertEquals(0, $this->logViewer->count());
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Sets the log storage path temporarily to a new directory
     */
    private function setupForTests(): void
    {
        if ( ! file_exists($this->path))
            mkdir($this->path, 0777, true);

        $this->logViewer->setPath($this->path);
        $this->app['config']->set(['log-viewer.storage-path' => $this->path]);
    }
}
