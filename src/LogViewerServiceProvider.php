<?php namespace Arcanedev\LogViewer;

use Arcanedev\Support\Laravel\PackageServiceProvider;

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
    protected $package = 'log-viewer';

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $basePath   = __DIR__ . '/..';

        $this->registerViews($basePath);
        $this->registerConfig($basePath);
        $this->registerTranslations($basePath);

        $this->app->register(Providers\RouteServiceProvider::class);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->register(Providers\UtilitiesServiceProvider::class);

        $this->registerLogViewer();

        $this->app->register(Providers\CommandsServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['log-viewer'];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Resources
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register and publishes configs
     *
     * @param  string  $basePath
     */
    private function registerConfig($basePath)
    {
        $configPath = realpath($basePath . "/config/{$this->package}.php");
        $this->mergeConfigFrom($configPath, $this->package);
        $this->publishes([
            $configPath => config_path("{$this->package}.php")
        ], 'config');
    }

    /**
     * Register and publishes Translations
     *
     * @param  string  $basePath
     */
    private function registerTranslations($basePath)
    {
        $langPath = $basePath . '/resources/lang';

        $this->loadTranslationsFrom($langPath, $this->package);
        $this->publishes([
            $langPath => base_path("resources/lang/arcanedev/{$this->package}"),
        ], 'translations');
    }

    /**
     * Register and published Views
     *
     * @param  string  $basePath
     */
    private function registerViews($basePath)
    {
        $viewsPath = $basePath . '/resources/views';

        $this->loadViewsFrom($viewsPath, $this->package);
        $this->publishes([
            $viewsPath => base_path("resources/views/arcanedev/{$this->package}"),
        ], 'views');
    }

    /* ------------------------------------------------------------------------------------------------
     |  Services
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the log data class.
     */
    private function registerLogViewer()
    {
        $this->app->singleton($this->package, function ($app) {
            /**
             * @var  Contracts\FactoryInterface     $factory
             * @var  Contracts\FilesystemInterface  $filesystem
             * @var  Contracts\LogLevelsInterface   $levels
             */
            $factory    = $app[$this->package . '.factory'];
            $filesystem = $app[$this->package . '.filesystem'];
            $levels     = $app[$this->package . '.levels'];

            return new LogViewer($factory, $filesystem, $levels);
        });

        $this->app->alias($this->package,                      LogViewer::class);
        $this->app->alias(Contracts\LogViewerInterface::class, LogViewer::class);

        // Registering the Facade
        $this->addFacade('LogViewer', Facades\LogViewer::class);
    }
}
