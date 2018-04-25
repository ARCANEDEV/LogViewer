<?php namespace Arcanedev\LogViewer\Commands;

use Arcanedev\LogViewer\Contracts\LogViewer;
use Illuminate\FileSystem;
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

    protected $logviewer;

    public function __construct(Logviewer $logviewer)
    {
        $this->logviewer = $logviewer;
        parent::__construct($logviewer);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        dd(config('log-viewer.storage-path'));
    }

}
