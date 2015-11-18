<?php namespace Arcanedev\LogViewer\Tests\Providers;

use Arcanedev\LogViewer\Providers\UtilitiesServiceProvider;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     UtilitiesServiceProviderTest
 *
 * @package  Arcanedev\LogViewer\Tests\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class UtilitiesServiceProviderTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var UtilitiesServiceProvider */
    private $provider;

    /**
     * The services provided by the provider.
     *
     * @var array
     */
    private $utilities = [
        'arcanedev.log-viewer.levels',
        'Arcanedev\LogViewer\Contracts\LogLevelsInterface',
        'arcanedev.log-viewer.styler',
        'Arcanedev\LogViewer\Contracts\LogStylerInterface',
        'arcanedev.log-viewer.menu',
        'Arcanedev\LogViewer\Contracts\LogMenuInterface',
        'arcanedev.log-viewer.filesystem',
        'Arcanedev\LogViewer\Contracts\FilesystemInterface',
        'arcanedev.log-viewer.factory',
        'Arcanedev\LogViewer\Contracts\FactoryInterface',
        'arcanedev.log-viewer.checker',
        'Arcanedev\LogViewer\Contracts\LogCheckerInterface',
    ];

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(
            'Arcanedev\\LogViewer\\Providers\\UtilitiesServiceProvider'
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
    public function it_can_get_provides()
    {
        $provided = $this->provider->provides();

        $this->assertCount(count($this->utilities), $provided);
        $this->assertEquals($this->utilities, $provided);
    }
}
