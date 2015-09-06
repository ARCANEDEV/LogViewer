<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Providers\RouteServiceProvider;
use Illuminate\Foundation\Application;

/**
 * Class     RoutesTest
 *
 * @package  Arcanedev\LogViewer\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class RoutesTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->register(RouteServiceProvider::class)->boot($app['router']);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_see_the_dashboard_page()
    {
        $response = $this->route('GET', 'log-viewer::dashboard');

        $this->assertResponseOk();
        $this->assertContains(
            '<h1 class="page-header">Dashboard</h1>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_can_show_a_log_page()
    {
        $date     = '2015-01-01';
        $response = $this->route('GET', 'log-viewer::logs.show', [$date]);

        $this->assertResponseOk();
        $this->assertContains(
            '<h1 class="page-header">Log [' . $date . ']</h1>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_can_see_a_filtered_log_entries_page()
    {
        $date     = '2015-01-01';
        $level    = 'error';
        $response = $this->route('GET', 'log-viewer::logs.filter', [$date, $level]);

        $this->assertResponseOk();
        $this->assertContains(
            '<h1 class="page-header">Log [' . $date . ']</h1>',
            $response->getContent()
        );
        // TODO: Add more assertion to check if the log entries is filtered by a level
    }

    /** @test */
    public function it_can_download_a_log_page()
    {
        $date     = '2015-01-01';
        $response = $this->route('GET', 'log-viewer::logs.download', [$date]);

        $this->assertResponseOk();

        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response
        );
        /** @var \Symfony\Component\HttpFoundation\BinaryFileResponse $response */
        $this->assertEquals(
            "laravel-$date.log", $response->getFile()->getFilename()
        );
    }
}
