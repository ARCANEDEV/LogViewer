<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests;

/**
 * Class     ConsoleThemeTest
 *
 * Renders every page of the opt-in `console` theme through real routes to catch
 * Blade / view errors, undefined variables, and helper misuse.
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class ConsoleThemeTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('log-viewer.theme', 'console');
    }

    /** @test */
    public function it_renders_the_dashboard(): void
    {
        $this->get(route('log-viewer::dashboard'))
             ->assertOk()
             ->assertSee('LogViewer')
             ->assertSee('data-theme');
    }

    /** @test */
    public function it_renders_the_logs_list(): void
    {
        $this->get(route('log-viewer::logs.list'))
             ->assertOk()
             ->assertSee('quick-filter', false);
    }

    /** @test */
    public function it_renders_a_log_page_with_filters(): void
    {
        $date = '2015-01-01';

        $this->get(route('log-viewer::logs.show', [$date]))
             ->assertOk()
             ->assertSee($date)
             ->assertSee('date-switcher', false)   // file switcher
             ->assertSee('quick-filter', false)    // live page filter
             ->assertSee('chip lvl', false);       // level chip bar
    }

    /** @test */
    public function it_renders_a_filtered_and_searched_log_page(): void
    {
        $date = '2015-01-01';

        $this->get(route('log-viewer::logs.filter', [$date, 'error']))->assertOk();

        $this->get(route('log-viewer::logs.search', ['date' => $date, 'level' => 'all', 'query' => 'foo']))
             ->assertOk();
    }
}
