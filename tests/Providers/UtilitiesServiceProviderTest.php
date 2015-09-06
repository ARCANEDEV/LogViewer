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

    /** @var array */
    private $utilities = [
        'arcanedev.log-viewer.levels',
        'arcanedev.log-viewer.menu',
        'arcanedev.log-viewer.filesystem',
        'arcanedev.log-viewer.factory',
        'arcanedev.log-viewer.styler',
    ];

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(UtilitiesServiceProvider::class);
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

        $this->assertCount(count($this->utilities), $provided);
        $this->assertEquals($this->utilities, $provided);
    }
}
