<?php namespace Arcanedev\LogViewer\Commands;

use Arcanedev\LogViewer\Bases\Command;
use Arcanedev\LogViewer\LogViewerServiceProvider;

/**
 * Class PublishCommand
 * @package Arcanedev\LogViewer\Commands
 */
class PublishCommand extends Command
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:publish
            {--tag= : One or many tags that have assets you want to publish.}
            {--force : Overwrite any existing files.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all LogViewer resources and config files';

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $args['--provider'] = LogViewerServiceProvider::class;

        if ((bool) $this->option('force')) {
            $args['--force']    = true;
        }

        $tag = $this->option('tag');

        if ( ! is_null($tag)) {
            $args['--tag'] = [$tag];
        }

        $this->call('vendor:publish', $args);
    }
}
