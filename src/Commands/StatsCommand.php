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

    /**
     * Table style
     * Supported: default, borderless, compact, symfony-style-guide
     *
     * @var string
     */
    private $style = 'default';

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
        $rows  = [];
        $stats = $this->logViewer->stats();

        foreach ($stats as $date => $levels) {
            $rows[] = array_merge_recursive(compact('date'), $levels);
        }

        $rows[] = $this->getTableSeparator();
        $rows[] = $this->getTotals($stats);

        // Display Data
        $this->displayLogViewer();
        $this->table(
            ['Date', 'All', 'Emergency', 'Alert', 'Critical', 'Error', 'Warning', 'Notice', 'Info', 'Debug'],
            $rows,
            $this->style
        );
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Calculate the total
     *
     * @param  array  $stats
     *
     * @return array
     */
    private function getTotals(array $stats)
    {
        $total = [];

        foreach ($stats as $date => $levels) {
            foreach ($levels as $level => $count) {
                if (isset($total[$level])) {
                    $total[$level] += $count;
                } else {
                    $total[$level] = $count;
                }
            }
        }

        return array_merge([count($stats) . ' log file(s)'], $total);
    }
}
