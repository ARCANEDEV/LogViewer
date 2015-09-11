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
        return __DIR__ . '/..';
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
        $this->registerLogViewerFacade();
        $this->app->register('Arcanedev\\LogViewer\\Providers\\CommandsServiceProvider');
    }

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->publishConfig();
        $this->registerViews();
        $this->registerTranslations();
        $this->app->register('Arcanedev\\LogViewer\\Providers\\RouteServiceProvider');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['arcanedev.log-viewer'];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Resources
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get config file path.
     *
     * @return string
     */
    protected function getConfigFile()
    {
        return realpath($this->getBasePath() . "/config/{$this->package}.php");
    }

    /**
     * Register configs.
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom($this->getConfigFile(), $this->package);
    }

    /**
     * Publishes configs.
     */
    private function publishConfig()
    {
        $this->publishes([
            $this->getConfigFile() => config_path("{$this->package}.php")
        ], 'config');
    }

    /**
     * Register and publishes Translations.
     */
    private function registerTranslations()
    {
        $langPath = $this->getBasePath() . '/resources/lang';

        $this->loadTranslationsFrom($langPath, $this->package);
        $this->publishes([
            $langPath => base_path('resources/lang/vendor/' . $this->package),
        ], 'translations');
    }

    /**
     * Register and published Views.
     */
    private function registerViews()
    {
        $viewsPath = $this->getBasePath() . '/resources/views';

        $this->loadViewsFrom($viewsPath, $this->package);
        $this->publishes([
            $viewsPath => base_path('resources/views/vendor/' . $this->package),
        ], 'views');
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
        $this->app->singleton("{$this->vendor}.log-viewer", function () {
            /**
             * @var  Contracts\FactoryInterface     $factory
             * @var  Contracts\FilesystemInterface  $filesystem
             * @var  Contracts\LogLevelsInterface   $levels
             */
            $factory    = $this->getUtility('factory');
            $filesystem = $this->getUtility('filesystem');
            $levels     = $this->getUtility('levels');

            return new LogViewer($factory, $filesystem, $levels);
        });
    }

    /**
     * Register LogViewer Facade
     */
    private function registerLogViewerFacade()
    {
        // Registering the Facade
        $this->addFacade(
            $this->app['config']->get('log-viewer.facade', 'LogViewer'),
            'Arcanedev\\LogViewer\\Facades\\LogViewer'
        );
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get a utility instance.
     *
     * @param  string  $name
     *
     * @return mixed
     */
    private function getUtility($name)
    {
        return $this->app["{$this->vendor}.{$this->package}.$name"];
    }
}
