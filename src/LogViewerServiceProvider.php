<?php namespace Arcanedev\LogViewer;

use Arcanedev\Support\PackageServiceProvider as ServiceProvider;

/**
 * Class     LogViewerServiceProvider
 *
 * @package  Arcanedev\LogViewer
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Vendor name.
     *
     * @var string
     */
    protected $vendor  = 'arcanedev';

    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'log-viewer';

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the base path.
     *
     * @return string
     */
    public function getBasePath()
    {
        return dirname(__DIR__);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerConfig();

        $this->app->register('Arcanedev\\LogViewer\\Providers\\UtilitiesServiceProvider');
        $this->registerLogViewer();
        $this->registerAliases();

        if ($this->app->runningInConsole()) {
            $this->app->register('Arcanedev\\LogViewer\\Providers\\CommandsServiceProvider');
        }
    }

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->publishConfig();
        $this->publishViews();
        $this->publishTranslations();
        $this->app->register('Arcanedev\\LogViewer\\Providers\\RouteServiceProvider');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'arcanedev.log-viewer',
            'Arcanedev\\LogViewer\\Contracts\\LogViewerInterface',
        ];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Services Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the log data class.
     */
    private function registerLogViewer()
    {
        $this->singleton(
            'arcanedev.log-viewer',
            'Arcanedev\\LogViewer\\LogViewer'
        );

        $this->bind(
            'Arcanedev\\LogViewer\\Contracts\\LogViewerInterface',
            'arcanedev.log-viewer'
        );

        // Registering the Facade
        $this->alias(
            $this->app['config']->get('log-viewer.facade', 'LogViewer'),
            'Arcanedev\\LogViewer\\Facades\\LogViewer'
        );
    }
}
