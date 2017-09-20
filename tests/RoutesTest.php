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
        $response = $this->get(route('log-viewer::dashboard'));

        $response->assertSuccessful();

        $this->assertContains(
            '<h1 class="page-header">Dashboard</h1>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_can_see_logs_page()
    {
        $response = $this->get(route('log-viewer::logs.list'));

        $response->assertSuccessful();
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
        $response = $this->get(route('log-viewer::logs.show', [$date]));

        $response->assertSuccessful();
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
        $response = $this->get(route('log-viewer::logs.filter', [$date, $level]));

        $response->assertSuccessful();
        $this->assertContains(
            '<h1 class="page-header">Log ['.$date.']</h1>',
            $response->getContent()
        );
        // TODO: Add more assertion => log entries is filtered by a level
    }

    /** @test */
    public function it_can_search_if_log_entries_contains_same_header_page()
    {
        $date     = '2015-01-01';
        $level    = 'all';
        $query    = 'This is an error log.';
        $response = $this->get(route('log-viewer::logs.search', compact('date', 'level', 'query')));

        $response->assertSuccessful();

        /** @var \Illuminate\View\View $view */
        $view = $response->getOriginalContent();

        $this->assertArrayHasKey('entries', $view->getData());

        /** @var  \Illuminate\Pagination\LengthAwarePaginator  $entries */
        $entries = $view->getData()['entries'];

        $this->assertCount(1, $entries);
    }

    /** @test */
    public function it_must_redirect_on_all_level()
    {
        $date     = '2015-01-01';
        $level    = 'all';
        $response = $this->get(route('log-viewer::logs.filter', [$date, $level]));

        $this->assertTrue($response->isRedirection());
        $this->assertEquals(302, $response->getStatusCode());
        // TODO: Add more assertion to check the redirect url
    }

    /** @test */
    public function it_can_download_a_log_page()
    {
        $date     = '2015-01-01';
        $response = $this->get(route('log-viewer::logs.download', [$date]));

        $response->assertSuccessful();

        /** @var  \Symfony\Component\HttpFoundation\BinaryFileResponse  $base */
        $base = $response->baseResponse;

        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class, $base
        );
        $this->assertEquals(
            "laravel-$date.log",
            $base->getFile()->getFilename()
        );
    }

    /** @test */
    public function it_can_delete_a_log()
    {
        $date = date('Y-m-d');

        $this->createDummyLog($date);

        $server   = ['HTTP_X-Requested-With' => 'XMLHttpRequest'];

        $response = $this->call('DELETE', route('log-viewer::logs.delete', compact('date')), [], [], [], $server);
        $response->assertExactJson(['result' => 'success']);
    }

    /** @test */
    public function it_must_throw_log_not_found_exception_on_show()
    {
        $response = $this->get(route('log-viewer::logs.show', ['0000-00-00']));

        $code    = $response->getStatusCode();
        $message = $response->exception->getMessage();

        $this->assertInstanceOf(
            \Symfony\Component\HttpKernel\Exception\HttpException::class,
            $response->exception
        );

        $this->assertSame(404, $code);
        $this->assertSame('Log not found in this date [0000-00-00]', $message);
    }

    /** @test */
    public function it_must_throw_log_not_found_exception_on_delete()
    {
        try {
            $response = $this->call('DELETE', route('log-viewer::logs.delete', ['0000-00-00']), [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
            $response->assertExactJson(['message' => 'Server Error']);
        }
        catch(\Exception $exception) {
            $this->assertInstanceOf(\Arcanedev\LogViewer\Exceptions\FilesystemException::class, $exception);
            $this->assertStringStartsWith('The log(s) could not be located at : ', $exception->getMessage());
        }
    }

    /** @test */
    public function it_must_throw_method_not_allowed_on_delete()
    {
        $response = $this->delete(route('log-viewer::logs.delete'));

        $response->assertStatus(405);
        $this->assertInstanceOf(
            \Symfony\Component\HttpKernel\Exception\HttpException::class,
            $response->exception
        );

        $this->assertSame('Method Not Allowed', $response->exception->getMessage());
    }
}
