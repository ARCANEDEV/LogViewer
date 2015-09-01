<?php namespace Arcanedev\LogViewer\Tests;
use Arcanedev\LogViewer\LogViewerServiceProvider;

/**
 * Class LogViewerServiceProviderTest
 * @package Arcanedev\LogViewer\Tests
 */
class LogViewerServiceProviderTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogViewerServiceProvider */
    private $provider;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(LogViewerServiceProvider::class);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_get_provides_list()
    {
        $provided = $this->provider->provides();
        $defaults = [
            'log-viewer',
            'log-viewer.levels',
            'log-viewer.factory',
            'log-viewer.filesystem',
        ];

        $this->assertCount(count($defaults), $provided);
        $this->assertEquals($defaults, $provided);
    }
}
