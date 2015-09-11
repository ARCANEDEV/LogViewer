<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Contracts\LogLevelsInterface;
use Arcanedev\LogViewer\Contracts\LogStylerInterface;
use Arcanedev\LogViewer\Utilities\Factory;
use Arcanedev\LogViewer\Utilities\Filesystem;
use Arcanedev\LogViewer\Utilities\LogChecker;
use Arcanedev\LogViewer\Utilities\LogLevels;
use Arcanedev\LogViewer\Utilities\LogMenu;
use Arcanedev\LogViewer\Utilities\LogStyler;
use Arcanedev\Support\ServiceProvider;
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
    /** @var string */
    protected $vendor  = 'arcanedev';

    /** @var string */
    protected $package = 'log-viewer';

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
        $this->registerStyler();
        $this->registerLogMenu();
        $this->registerFilesystem();
        $this->registerFactory();
        $this->registerChecker();
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
     * Register the log styler.
     */
    private function registerStyler()
    {
        $this->registerUtility('styler', function ($app) {
            /** @var  Config  $config */
            $config = $app['config'];

            return new LogStyler($config);
        });
    }

    /**
     * Register the log menu builder.
     */
    private function registerLogMenu()
    {
        $this->registerUtility('menu', function ($app) {
            /**
             * @var  Config              $config
             * @var  LogStylerInterface  $trans
             */
            $config = $app['config'];
            $styler  = $this->getUtility($app, 'styler');

            return new LogMenu($config, $styler);
        });
    }

    /**
     * Register the log filesystem class.
     */
    private function registerFilesystem()
    {
        $this->registerUtility('filesystem', function ($app) {
            /**
             * @var  \Illuminate\Filesystem\Filesystem  $files
             */
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
             * @var  FilesystemInterface  $filesystem
             * @var  LogLevelsInterface   $level
             */
            $filesystem = $this->getUtility($app, 'filesystem');
            $level      = $this->getUtility($app, 'levels');

            return new Factory($filesystem, $level);
        });
    }

    private function registerChecker()
    {
        $this->registerUtility('checker', function ($app) {
            /**
             * @var  Config               $config
             * @var  FilesystemInterface  $filesystem
             */
            $config     = $app['config'];
            $filesystem = $this->getUtility($app, 'filesystem');

            return new LogChecker($config, $filesystem);
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
        return "{$this->vendor}.{$this->package}.$name";
    }
}
