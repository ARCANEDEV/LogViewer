<?php namespace Arcanedev\LogViewer\Tests;

/**
 * Class     RoutesTest
 *
 * @package  Arcanedev\LogViewer\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @todo:    Find a way to test the route Classes with testbench (Find another tool if it's impossible).
 */
class RoutesTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_see_dashboard_page()
    {
        $response = $this->route('GET', 'log-viewer::dashboard');

        $this->assertResponseOk();
        $this->assertContains(
            '<h1 class="page-header">Dashboard</h1>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_can_see_logs_page()
    {
        $response = $this->route('GET', 'log-viewer::logs.list');

        $this->assertResponseOk();
        $this->assertContains(
            '<h1 class="page-header">Logs</h1>',
            $response->getContent()
        );
        // TODO: Add more assertion => list all logs
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
        // TODO: Add more assertion => list all log entries
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
        // TODO: Add more assertion => log entries is filtered by a level
    }

    /** @test */
    public function it_must_redirect_on_all_level()
    {
        $date     = '2015-01-01';
        $level    = 'all';
        $response = $this->route('GET', 'log-viewer::logs.filter', [$date, $level]);

        $this->assertTrue($response->isRedirection());
        $this->assertEquals(302, $response->getStatusCode());
        // TODO: Add more assertion to check the redirect url
    }

    /** @test */
    public function it_can_download_a_log_page()
    {
        $date     = '2015-01-01';
        /** @var \Symfony\Component\HttpFoundation\BinaryFileResponse $response */
        $response = $this->route('GET', 'log-viewer::logs.download', [$date]);

        $this->assertResponseOk();
        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class,
            $response
        );
        $this->assertEquals(
            "laravel-$date.log",
            $response->getFile()->getFilename()
        );
    }

    /** @test */
    public function it_can_delete_a_log()
    {
        $date = date('Y-m-d');

        $this->createDummyLog($date);

        $server   = ['HTTP_X-Requested-With' => 'XMLHttpRequest'];

        /** @var \Illuminate\Http\JsonResponse $response */
        $response = $this->route('DELETE', 'log-viewer::logs.delete', compact('date'), [], [], [], $server);
        $data     = $response->getData(true);

        $this->assertResponseOk();
        $this->assertArrayHasKey('result', $data);
        $this->assertEquals($data['result'], 'success');
    }

    /** @test */
    public function it_must_throw_log_not_found_exception_on_show()
    {
        try {
            $this->route('GET', 'log-viewer::logs.show', ['0000-00-00']);

            $code    = $this->response->getStatusCode();
            $message = $this->response->exception->getMessage();

            $this->assertInstanceOf(
                \Symfony\Component\HttpKernel\Exception\HttpException::class,
                $this->response->exception
            );
        }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $code    = $e->getStatusCode();
            $message = $e->getMessage();
        }

        $this->assertSame(404, $code);
        $this->assertSame('Log not found in this date [0000-00-00]', $message);
    }

    /**
     * @test
     *
     * @expectedException
     */
    public function it_must_throw_log_not_found_exception_on_delete()
    {
        try {
            $server = ['HTTP_X-Requested-With' => 'XMLHttpRequest'];

            $this->route('DELETE', 'log-viewer::logs.delete', ['0000-00-00'], [], [], [], $server);
        }
        catch(\Exception $exception) {
            $this->assertInstanceOf(
                \Arcanedev\LogViewer\Exceptions\FilesystemException::class,
                $exception
            );
            $this->assertStringStartsWith('The log(s) could not be located at : ', $exception->getMessage());
        }
    }

    /** @test */
    public function it_must_throw_method_not_allowed_on_delete()
    {
        try {
            $this->route('DELETE', 'log-viewer::logs.delete');

            $code    = $this->response->getStatusCode();
            $message = $this->response->exception->getMessage();

            $this->assertInstanceOf(
                \Symfony\Component\HttpKernel\Exception\HttpException::class,
                $this->response->exception
            );
        }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $code    = $e->getStatusCode();
            $message = $e->getMessage();
        }

        $this->assertSame(405, $code);
        $this->assertSame('Method Not Allowed', $message);
    }
}
