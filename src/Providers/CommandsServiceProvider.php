<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\LogViewer\Commands;
use Arcanedev\Support\Providers\CommandServiceProvider as ServiceProvider;

/**
 * Class     CommandsServiceProvider
 *
 * @package  Arcanedev\LogViewer\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class CommandsServiceProvider extends ServiceProvider
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        Commands\PublishCommand::class,
        Commands\StatsCommand::class,
        Commands\CheckCommand::class,
        Commands\ClearCommand::class,
    ];
}
