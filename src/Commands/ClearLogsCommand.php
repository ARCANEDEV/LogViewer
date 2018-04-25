<?php namespace Arcanedev\LogViewer\Commands;

use File;
use Arcanedev\LogViewer\Contracts\LogViewer;

/**
 * Class     ClearLogsCommand
 *
 * @package  Arcanedev\LogViewer\Commands
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class ClearLogsCommand extends Command
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
    protected $name = 'log-viewer:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all generated log files';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:clear';

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('This will delete all the log files, Do you wish to continue? [yes|no]'))
        {
            File::cleanDirectory(config('log-viewer.storage-path'));
            $this->info('Successfully Cleared The Logs');
        }
        else
        {
            $this->info('Operation Cancelled');
        }
    }

}
