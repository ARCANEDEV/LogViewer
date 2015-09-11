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
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var CommandsServiceProvider */
    private $provider;

    /** @var array */
    private $commands = [
        'arcanedev.log-viewer.commands.check',
        'arcanedev.log-viewer.commands.publish',
        'arcanedev.log-viewer.commands.stats',
    ];

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(
            'Arcanedev\\LogViewer\\Providers\\CommandsServiceProvider'
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->provider);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_get_provides_list()
    {
        $provided = $this->provider->provides();

        $this->assertCount(count($this->commands), $provided);
        $this->assertEquals($this->commands, $provided);
    }
}
