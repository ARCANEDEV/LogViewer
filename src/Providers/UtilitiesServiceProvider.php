<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\LogViewer\Contracts\FactoryInterface;
use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Contracts\LogLevelsInterface;
use Arcanedev\LogViewer\Contracts\LogMenuInterface;
use Arcanedev\LogViewer\Utilities\Factory;
use Arcanedev\LogViewer\Utilities\Filesystem;
use Arcanedev\LogViewer\Utilities\LogLevels;
use Arcanedev\LogViewer\Utilities\LogMenu;
use Arcanedev\Support\Laravel\ServiceProvider;
use Closure;
use Illuminate\Translation\Translator;
use Illuminate\Config\Repository as Config;

/**
 * Class     UtilitiesServiceProvider
 *
 * @package  Arcanedev\LogViewer\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class UtilitiesServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var array */
    private $utilities = [];

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerLogLevels();
        $this->registerLogMenu();
        $this->registerFilesystem();
        $this->registerFactory();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return $this->utilities;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Utility Registrations
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the log levels.
     */
    private function registerLogLevels()
    {
        $this->registerUtility('levels', function ($app) {
            /** @var Translator $trans */
            $trans  = $app['translator'];

            return new LogLevels($trans);
        });

        $this->app->alias('arcanedev.log-viewer.levels', LogLevels::class);
        $this->app->alias(LogLevelsInterface::class,     LogLevels::class);
    }

    /**
     * Register the log menu builder.
     */
    private function registerLogMenu()
    {
        $this->registerUtility('menu', function ($app) {
            /**
             * @var Config     $config
             * @var Translator $trans
             */
            $config = $app['config'];
            $trans  = $app['translator'];

            return new LogMenu($config, $trans);
        });

        $this->app->alias('arcanedev.log-viewer.menu', LogMenu::class);
        $this->app->alias(LogMenuInterface::class,     LogMenu::class);
    }

    /**
     * Register the log filesystem class.
     */
    private function registerFilesystem()
    {
        $this->registerUtility('filesystem', function ($app) {
            $files = $app['files'];
            $path  = storage_path('logs');

            return new Filesystem($files, $path);
        });

        $this->app->alias('arcanedev.log-viewer.filesystem', Filesystem::class);
        $this->app->alias(FilesystemInterface::class,        Filesystem::class);
    }

    /**
     * Register the log factory class.
     */
    private function registerFactory()
    {
        $this->registerUtility('factory', function ($app) {
            /**
             * @var FilesystemInterface $filesystem
             * @var LogLevelsInterface  $level
             */
            $filesystem = $app['arcanedev.log-viewer.filesystem'];
            $level      = $app['arcanedev.log-viewer.levels'];

            return new Factory($filesystem, $level);
        });

        $this->app->alias('arcanedev.log-viewer.factory', Factory::class);
        $this->app->alias(FactoryInterface::class,        Factory::class);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the utility.
     *
     * @param  string   $name
     * @param  Closure  $callback
     */
    private function registerUtility($name, Closure $callback)
    {
        $name = "arcanedev.log-viewer.$name";

        $this->app->singleton($name, $callback);

        $this->utilities[] = $name;
    }
}
