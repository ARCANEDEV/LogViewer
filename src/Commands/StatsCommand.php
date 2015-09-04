<?php namespace Arcanedev\LogViewer\Commands;

use Arcanedev\LogViewer\Bases\Command;
use Arcanedev\LogViewer\Utilities\StatsTable;

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
        // Load Data
        $stats   = $this->logViewer->statsTable('en');

        $rows    = $stats->rows();
        $rows[]  = $this->getTableSeparator();
        $rows[]  = $this->prepareFooter($stats);

        // Display Data
        $this->displayLogViewer();
        $this->table($stats->header(), $rows);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Prepare footer
     *
     * @param  StatsTable  $stats
     *
     * @return array
     */
    private function prepareFooter(StatsTable $stats)
    {
        $files = [
            'count' => count($stats->rows()) . ' log file(s)'
        ];

        return $files + $stats->footer();
    }
}
