<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Commands;

use Arcanedev\LogViewer\LogViewerServiceProvider;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class     PublishCommand
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class PublishCommand extends Command
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name      = 'log-viewer:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all LogViewer resources and config files';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:publish
            {--tag= : One or many tags that have assets you want to publish.}
            {--force : Overwrite any existing files.}';

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $args = [
            '--provider' => LogViewerServiceProvider::class,
        ];

        if ((bool) $this->option('force')) {
            $args['--force'] = true;
        }

        $args['--tag'] = [$this->option('tag')];

        $this->displayLogViewer();
        $this->call('vendor:publish', $args);
    }

    /**
     * Get the console command options.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    protected function getOptions()
    {
        return [
            ['tag', 't', InputOption::VALUE_OPTIONAL, 'One or many tags that have assets you want to publish.', ''],
            ['force', 'f', InputOption::VALUE_OPTIONAL, 'Overwrite any existing files.', false],
        ];
    }
}
