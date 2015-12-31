<?php namespace Arcanedev\LogViewer\Commands;

use Arcanedev\LogViewer\Bases\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class     PublishCommand
 *
 * @package  Arcanedev\LogViewer\Commands
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class PublishCommand extends Command
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
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

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $args = [
            '--provider' => 'Arcanedev\\LogViewer\\LogViewerServiceProvider'
        ];

        if ((bool) $this->option('force')) {
            $args['--force']    = true;
        }

        $tag = $this->option('tag');

        if ( ! is_null($tag)) {
            $args['--tag'] = version_compare(laravel_version(), '5.1.0', '>=') ? [$tag] : $tag;
        }

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
