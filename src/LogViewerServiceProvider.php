<?php namespace Arcanedev\LogViewer;

use Arcanedev\Support\Providers\PackageServiceProvider;

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
    public function register(): void
    {
        parent::register();

        $this->registerConfig();

        $this->registerProvider(Providers\RouteServiceProvider::class);

        $this->registerCommands([
            Commands\PublishCommand::class,
            Commands\StatsCommand::class,
            Commands\CheckCommand::class,
            Commands\ClearCommand::class,
        ]);
    }

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->publishViews();
        $this->publishTranslations();
    }
}
