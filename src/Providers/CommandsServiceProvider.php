<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\LogViewer\Commands;
use Arcanedev\LogViewer\LogViewer;
use Arcanedev\Support\ServiceProvider;
use Closure;

/**
 * Class     CommandsServiceProvider
 *
 * @package  Arcanedev\LogViewer\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class CommandsServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var string */
    protected $vendor   = 'arcanedev';

    /** @var string */
    protected $package  = 'log-viewer';

    /** @var array */
    protected $commands = [];

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
        $this->registerCheckCommand();
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
     * Register the check command.
     */
    private function registerCheckCommand()
    {
        $this->registerCommand('check', function () {
            $logViewer = $this->getLogViewer();

            return new Commands\CheckCommand($logViewer);
        });
    }

    /**
     * Register the publish command.
     */
    private function registerPublishCommand()
    {
        $this->registerCommand('publish', function () {
            $logViewer = $this->getLogViewer();

            return new Commands\PublishCommand($logViewer);
        });
    }

    /**
     * Register the stats command.
     */
    private function registerStatsCommand()
    {
        $this->registerCommand('stats', function () {
            $logViewer = $this->getLogViewer();

            return new Commands\StatsCommand($logViewer);
        });
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the LogViewer instance.
     *
     * @return LogViewer
     */
    private function getLogViewer()
    {
        return $this->app['arcanedev.log-viewer'];
    }

    /**
     * Register a command.
     *
     * @param  string   $name
     * @param  Closure  $callback
     */
    protected function registerCommand($name, Closure $callback)
    {
        $command = "{$this->vendor}.{$this->package}.commands.$name";

        $this->app->singleton($command, $callback);

        $this->commands[] = $command;
    }
}
