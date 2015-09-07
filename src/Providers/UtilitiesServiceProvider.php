<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Contracts\LogLevelsInterface;
use Arcanedev\LogViewer\Utilities\Factory;
use Arcanedev\LogViewer\Utilities\Filesystem;
use Arcanedev\LogViewer\Utilities\LogLevels;
use Arcanedev\LogViewer\Utilities\LogMenu;
use Arcanedev\LogViewer\Utilities\LogStyler;
use Arcanedev\Support\Laravel\ServiceProvider;
use Closure;
use Illuminate\Config\Repository as Config;
use Illuminate\Foundation\Application;
use Illuminate\Translation\Translator;

/**
 * Class     UtilitiesServiceProvider
 *
 * @package  Arcanedev\LogViewer\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @todo     Refactoring
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
        $this->registerStyler();
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
    }

    /**
     * Register the log filesystem class.
     */
    private function registerFilesystem()
    {
        $this->registerUtility('filesystem', function ($app) {
            $files = $app['files'];

            return new Filesystem($files, storage_path('logs'));
        });
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
            $filesystem = $this->getUtility($app, 'filesystem');
            $level      = $this->getUtility($app, 'levels');

            return new Factory($filesystem, $level);
        });
    }

    /**
     * Register the log styler.
     */
    private function registerStyler()
    {
        $this->registerUtility('styler', function ($app) {
            $config = $app['config'];

            return new LogStyler($config);
        });
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the utility.
     *
     * @param  Application  $app
     * @param  string       $name
     *
     * @return mixed
     */
    private function getUtility($app, $name)
    {
        $name = $this->getUtilityName($name);

        return $app[$name];
    }

    /**
     * Register the utility.
     *
     * @param  string   $name
     * @param  Closure  $callback
     */
    private function registerUtility($name, Closure $callback)
    {
        $name = $this->getUtilityName($name);

        $this->app->singleton($name, $callback);

        $this->utilities[] = $name;
    }

    /**
     * Get utility name
     *
     * @param  string  $name
     *
     * @return string
     */
    private function getUtilityName($name)
    {
        return "arcanedev.log-viewer.$name";
    }
}
