<?php namespace Arcanedev\LogViewer\Tests\Commands;

use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     StatsCommandTest
 *
 * @package  Arcanedev\LogViewer\Tests\Commands
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class StatsCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_display_stats()
    {
        $code = $this->artisan('log-viewer:stats');

        static::assertSame(0, $code);
    }
}
