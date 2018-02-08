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
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_see_dashboard_page()
    {
        $response = $this->get(route('log-viewer::dashboard'));
        $response->assertSuccessful();

        static::assertContains(
            '<h1 class="page-header">Dashboard</h1>',
            $response->getContent()
        );
    }

    /** @test */
    public function it_can_see_logs_page()
    {
        $response = $this->get(route('log-viewer::logs.list'));
        $response->assertSuccessful();

        static::assertContains(
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

        static::assertContains(
            '<h1 class="page-header">Log ['.$date.']</h1>',
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

        static::assertContains(
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

        static::assertArrayHasKey('entries', $view->getData());

        /** @var  \Illuminate\Pagination\LengthAwarePaginator  $entries */
        $entries = $view->getData()['entries'];

        static::assertCount(1, $entries);
    }

    /** @test */
    public function it_must_redirect_on_all_level()
    {
        $date     = '2015-01-01';
        $level    = 'all';

        $response = $this->get(route('log-viewer::logs.filter', [$date, $level]));

        static::assertTrue($response->isRedirection());
        static::assertEquals(302, $response->getStatusCode());
        // TODO: Add more assertion to check the redirect url
    }

    /** @test */
    public function it_can_download_a_log_page()
    {
        $date = '2015-01-01';

        $response = $this->get(route('log-viewer::logs.download', [$date]));
        $response->assertSuccessful();

        /** @var  \Symfony\Component\HttpFoundation\BinaryFileResponse  $base */
        $base = $response->baseResponse;

        static::assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class, $base
        );
        static::assertEquals("laravel-$date.log", $base->getFile()->getFilename());
    }

    /** @test */
    public function it_can_delete_a_log()
    {
        $date = date('Y-m-d');

        $this->createDummyLog($date);

        $response = $this->call('DELETE', route('log-viewer::logs.delete', compact('date')), [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $response->assertExactJson(['result' => 'success']);
    }

    /** @test */
    public function it_must_throw_log_not_found_exception_on_show()
    {
        $response = $this->get(route('log-viewer::logs.show', ['0000-00-00']));

        static::assertInstanceOf(
            \Symfony\Component\HttpKernel\Exception\HttpException::class,
            $response->exception
        );

        static::assertSame(404, $response->getStatusCode());
        static::assertSame('Log not found in this date [0000-00-00]', $response->exception->getMessage());
    }

    /** @test */
    public function it_must_throw_log_not_found_exception_on_delete()
    {
        try {
            $response = $this->call('DELETE', route('log-viewer::logs.delete', ['0000-00-00']), [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
            $response->assertExactJson(['message' => 'Server Error']);
        }
        catch(\Exception $exception) {
            static::assertInstanceOf(\Arcanedev\LogViewer\Exceptions\FilesystemException::class, $exception);
            static::assertStringStartsWith('The log(s) could not be located at : ', $exception->getMessage());
        }
    }

    /** @test */
    public function it_must_throw_method_not_allowed_on_delete()
    {
        $response = $this->delete(route('log-viewer::logs.delete'));
        $response->assertStatus(405);

        static::assertInstanceOf(
            \Symfony\Component\HttpKernel\Exception\HttpException::class,
            $response->exception
        );
        static::assertSame('Method Not Allowed', $response->exception->getMessage());
    }
}
