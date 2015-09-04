<?php namespace Arcanedev\LogViewer;

use Arcanedev\Support\Laravel\ServiceProvider;

/**
 * Class LogViewerServiceProvider
 * @package Arcanedev\LogViewer
 */
class LogViewerServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $package    = 'log-viewer';
        $basePath   = __DIR__ . '/..';

        $this->registerConfig($package, $basePath);
        $this->registerTranslations($package, $basePath);
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
     * @param  string  $package
     * @param  string  $basePath
     */
    private function registerConfig($package, $basePath)
    {
        $configPath = realpath($basePath . "/config/$package.php");
        $this->mergeConfigFrom($configPath, $package);
        $this->publishes([
            $configPath => config_path("$package.php")
        ], 'config');
    }

    /**
     * Register and publishes Translations
     *
     * @param  string  $package
     * @param  string  $basePath
     */
    private function registerTranslations($package, $basePath)
    {
        $langPath = $basePath . '/resources/lang';

        $this->loadTranslationsFrom($langPath, $package);
        $this->publishes([
            $langPath => base_path("resources/lang/arcanedev/$package"),
        ], 'translations');
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
        $this->app->singleton('log-viewer', function ($app) {
            /**
             * @var  Contracts\FactoryInterface     $factory
             * @var  Contracts\FilesystemInterface  $filesystem
             * @var  Contracts\LogLevelsInterface   $levels
             */
            $factory    = $app['log-viewer.factory'];
            $filesystem = $app['log-viewer.filesystem'];
            $levels     = $app['log-viewer.levels'];

            return new LogViewer($factory, $filesystem, $levels);
        });

        $this->app->alias('log-viewer',                        LogViewer::class);
        $this->app->alias(Contracts\LogViewerInterface::class, LogViewer::class);

        // Registering the Facade
        $this->addAlias('LogViewer', Facades\LogViewer::class);
    }
}
