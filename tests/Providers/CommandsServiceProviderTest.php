<?php namespace Arcanedev\LogViewer\Tests\Providers;

use Arcanedev\LogViewer\Providers\CommandsServiceProvider;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     CommandsServiceProviderTest
 *
 * @package  Arcanedev\LogViewer\Tests\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class CommandsServiceProviderTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Providers\CommandsServiceProvider */
    private $provider;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp()
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(CommandsServiceProvider::class);
    }

    protected function tearDown()
    {
        unset($this->provider);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        $expectations = [
            \Illuminate\Support\ServiceProvider::class,
            \Arcanedev\Support\ServiceProvider::class,
            CommandsServiceProvider::class
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->provider);
        }
    }

    /** @test */
    public function it_can_provides()
    {
        $expected = [
            \Arcanedev\LogViewer\Commands\PublishCommand::class,
            \Arcanedev\LogViewer\Commands\StatsCommand::class,
            \Arcanedev\LogViewer\Commands\CheckCommand::class,
            \Arcanedev\LogViewer\Commands\ClearLogsCommand::class,
        ];

        static::assertSame($expected, $this->provider->provides());
    }
}
