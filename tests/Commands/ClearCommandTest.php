<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests\Commands;

use Arcanedev\LogViewer\Contracts\LogViewer as LogViewerContract;
use Arcanedev\LogViewer\Tests\TestCase;
use Illuminate\Support\Facades\File;

/**
 * Class     ClearCommandTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class ClearCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    private LogViewerContract $logViewer;

    private string $path;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->logViewer = $this->app->make(LogViewerContract::class);
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
        static::createDummyLog(date('Y-m-d'), storage_path('logs-to-clear'));

        static::assertGreaterThanOrEqual(1, $this->logViewer->count());

        $this
            ->artisan('log-viewer:clear')
            ->expectsQuestion('This will delete all the log files, Do you wish to continue?', 'yes')
            ->expectsOutput('Successfully cleared the logs!')
            ->assertSuccessful()
        ;

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
        File::ensureDirectoryExists($this->path);

        $this->logViewer->setPath($this->path);
        $this->app['config']->set(['log-viewer.storage-path' => $this->path]);
    }
}
