<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\LogViewer\Commands\PublishCommand;
use Arcanedev\LogViewer\Commands\StatsCommand;
use Arcanedev\Support\Laravel\ServiceProvider;
use Closure;

/**
 * Class CommandsServiceProvider
 * @package Arcanedev\LogViewer\Providers
 */
class CommandsServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var array */
    private $commands = [];

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->commands($this->commands);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPublishCommand();
        $this->registerStatsCommand();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return $this->commands;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Command registrations
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the publish command.
     */
    private function registerPublishCommand()
    {
        $this->registerCommand('publish', function ($app) {
            $logViewer = $app['log-viewer'];

            return new PublishCommand($logViewer);
        });
    }

    /**
     * Register the stats command.
     */
    public function registerStatsCommand()
    {
        $this->registerCommand('stats', function ($app) {
            $logViewer = $app['log-viewer'];

            return new StatsCommand($logViewer);
        });
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register a command.
     *
     * @param  string   $name
     * @param  Closure  $callback
     */
    private function registerCommand($name, Closure $callback)
    {
        $command = "log-viewer.commands.$name";

        $this->app->singleton($command, $callback);

        $this->commands[] = $command;
    }
}
