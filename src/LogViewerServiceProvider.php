<?php namespace Arcanedev\LogViewer;

use Arcanedev\LogViewer\Utilities\Factory;
use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Utilities\LogLevels;
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
        $basePath   = __DIR__ . '/..';

        // Register config
        $configPath = realpath($basePath . '/config/log-viewer.php');
        $this->mergeConfigFrom($configPath, 'log-viewer');
        $this->publishes([
            $configPath => config_path('log-viewer.php')
        ], 'config');

        $this->loadTranslationsFrom($basePath . '/resources/lang', 'log-viewer');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerLogLevels();
        $this->registerFilesystem();
        $this->registerFactory();
        $this->registerLogViewer();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'log-viewer',
            'log-viewer.levels',
            'log-viewer.factory',
            'log-viewer.filesystem',
        ];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Services
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the log data class.
     */
    private function registerLogLevels()
    {
        $this->app->singleton('log-viewer.levels', function () {
            return new LogLevels;
        });

        $this->app->alias('log-viewer.levels',                 Utilities\LogLevels::class);
        $this->app->alias(Contracts\LogLevelsInterface::class, Utilities\LogLevels::class);
    }

    /**
     * Register the log filesystem class.
     */
    private function registerFilesystem()
    {
        $this->app->singleton('log-viewer.filesystem', function ($app) {
            $files = $app['files'];
            $path  = storage_path('logs');

            return new Utilities\Filesystem($files, $path);
        });

        $this->app->alias('log-viewer.filesystem',              Utilities\Filesystem::class);
        $this->app->alias(Contracts\FilesystemInterface::class, Utilities\Filesystem::class);
    }

    /**
     * Register the log factory class.
     */
    private function registerFactory()
    {
        $this->app->singleton('log-viewer.factory', function ($app) {
            /** @var FilesystemInterface $filesystem */
            $filesystem = $app['log-viewer.filesystem'];

            return new Factory($filesystem);
        });

        $this->app->alias('log-viewer.factory',              Factory::class);
        $this->app->alias(Contracts\FactoryInterface::class, Factory::class);
    }

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
