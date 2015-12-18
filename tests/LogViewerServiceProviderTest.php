<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\LogViewerServiceProvider;

/**
 * Class     LogViewerServiceProviderTest
 *
 * @package  Arcanedev\LogViewer\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
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

        $this->provider = $this->app->getProvider('Arcanedev\\LogViewer\\LogViewerServiceProvider');
    }

    public function tearDown()
    {
        unset($this->provider);

        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $expectations = [
            'Illuminate\\Support\\ServiceProvider',
            'Arcanedev\\Support\\ServiceProvider',
            'Arcanedev\\Support\\PackageServiceProvider',
            'Arcanedev\\LogViewer\\LogViewerServiceProvider',
        ];

        foreach ($expectations as $expected) {
            $this->assertInstanceOf($expected, $this->provider);
        }
    }

    /** @test */
    public function it_can_provides()
    {
        $expected = [
            'arcanedev.log-viewer',
            'Arcanedev\\LogViewer\\Contracts\\LogViewerInterface',
        ];

        $this->assertEquals($expected, $this->provider->provides());
    }
}
