<?php namespace Arcanedev\LogViewer;

use Arcanedev\LogViewer\Utilities\Factory;
use Arcanedev\LogViewer\Utilities\Filesystem;
use Arcanedev\LogViewer\Entities\LogLevels;
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

        $this->app->alias('log-viewer.levels', LogLevels::class);
    }

    /**
     * Register the log filesystem class.
     */
    private function registerFilesystem()
    {
        $this->app->singleton('log-viewer.filesystem', function ($app) {
            $files = $app['files'];
            $path  = $app['path.storage'] . '/logs';

            return new Filesystem($files, $path);
        });

        $this->app->alias('log-viewer.filesystem', Filesystem::class);
    }

    /**
     * Register the log factory class.
     */
    private function registerFactory()
    {
        $this->app->singleton('log-viewer.factory', function ($app) {
            /** @var Filesystem $filesystem */
            $filesystem = $app['log-viewer.filesystem'];

            /** @var LogLevels $levels */
            $levels     = $app['log-viewer.levels'];

            return new Factory($filesystem, $levels->all());
        });

        $this->app->alias('log-viewer.factory', Factory::class);
    }

    /**
     * Register the log data class.
     */
    private function registerLogViewer()
    {
        $this->app->singleton('log-viewer', function ($app) {
            /**
             * @var  Factory     $factory
             * @var  Filesystem  $filesystem
             * @var  LogLevels   $levels
             */
            $factory    = $app['log-viewer.factory'];
            $filesystem = $app['log-viewer.filesystem'];
            $levels     = $app['log-viewer.levels'];

            return new LogViewer($factory, $filesystem, $levels);
        });

        $this->app->alias('log-viewer', LogViewer::class);
        $this->addAlias('LogViewer', Facades\LogViewer::class);
    }
}
