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
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'log-viewer';

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Register the service provider.
     */
    public function register()
    {
        parent::register();

        $this->registerConfig();

        $this->registerLogViewer();
        $this->registerAliases();

        $this->registerProviders([
            Providers\UtilitiesServiceProvider::class,
            Providers\RouteServiceProvider::class,
        ]);
        $this->registerConsoleServiceProvider(Providers\CommandsServiceProvider::class);
    }

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        parent::boot();

        $this->publishConfig();

        // Publish either Bootstrap 3 or 4 views based on publish tag
        //$this->publishViews();
        $this->publishes([
            __DIR__.'/../resources/views/bootstrap-3' => resource_path('views/vendor/log-viewer')
        ], 'bootstrap-3');

        $this->publishes([
            __DIR__.'/../resources/views/bootstrap-4' => resource_path('views/vendor/log-viewer')
        ], 'bootstrap-4');

        $this->publishTranslations();
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

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
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
