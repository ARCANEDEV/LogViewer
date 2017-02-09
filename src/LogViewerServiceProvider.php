<?php namespace Arcanedev\LogViewer;

use Arcanedev\Support\PackageServiceProvider;

/**
 * Class     LogViewerServiceProvider
 *
 * @package  Arcanedev\LogViewer
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerServiceProvider extends PackageServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
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
        parent::register();

        $this->registerConfig();
        $this->registerProvider(Providers\UtilitiesServiceProvider::class);
        $this->registerLogViewer();
        $this->registerAliases();
        $this->registerConsoleServiceProvider(Providers\CommandsServiceProvider::class);
    }

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        parent::boot();

        $this->publishConfig();
        $this->publishViews();
        $this->publishTranslations();
        $this->registerProvider(Providers\RouteServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Contracts\LogViewer::class,
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
        $this->singleton(Contracts\LogViewer::class, LogViewer::class);

        // Registering the Facade
        $this->alias(
            $this->config()->get('log-viewer.facade', 'LogViewer'),
            Facades\LogViewer::class
        );
    }
}
