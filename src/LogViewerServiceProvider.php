<?php namespace Arcanedev\LogViewer;

use Arcanedev\Support\Laravel\ServiceProvider;
use Illuminate\Config\Repository as Config;
use Illuminate\Translation\Translator;

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
        $this->registerLogLevels();
        $this->registerLogMenu();
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
     * Register the log levels.
     */
    private function registerLogLevels()
    {
        $this->app->singleton('log-viewer.levels', function ($app) {
            /** @var Translator $trans */
            $trans  = $app['translator'];

            return new Utilities\LogLevels($trans);
        });

        $this->app->alias('log-viewer.levels',                 Utilities\LogLevels::class);
        $this->app->alias(Contracts\LogLevelsInterface::class, Utilities\LogLevels::class);
    }

    /**
     * Register the log menu maker.
     */
    private function registerLogMenu()
    {
        $this->app->singleton('log-viewer.menu', function ($app) {
            /**
             * @var Config     $config
             * @var Translator $trans
             */
            $config = $app['config'];
            $trans  = $app['translator'];

            return new Utilities\LogMenu($config, $trans);
        });

        $this->app->alias('log-viewer.menu', Utilities\LogMenu::class);
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
            /** @var Contracts\FilesystemInterface $filesystem */
            $filesystem = $app['log-viewer.filesystem'];

            return new Utilities\Factory($filesystem);
        });

        $this->app->alias('log-viewer.factory',              Utilities\Factory::class);
        $this->app->alias(Contracts\FactoryInterface::class, Utilities\Factory::class);
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
