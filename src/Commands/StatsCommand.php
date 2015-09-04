<?php namespace Arcanedev\LogViewer\Commands;

use Arcanedev\LogViewer\Bases\Command;

/**
 * Class StatsCommand
 * @package Arcanedev\LogViewer\Commands
 */
class StatsCommand extends Command
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
    protected $signature   = 'log-viewer:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display stats of all logs.';

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $headers = [
            'Date',
            'All',
            'Emergency',
            'Alert',
            'Critical',
            'Error',
            'Warning',
            'Notice',
            'Info',
            'Debug'
        ];

        // TODO: Complete the implementation

        $stats = [];

        $this->table($headers, $stats);
    }
}
