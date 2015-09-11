<?php namespace Arcanedev\LogViewer\Bases;

use Arcanedev\LogViewer\Contracts\LogViewerInterface;
use Arcanedev\LogViewer\LogViewer;
use Illuminate\Console\Command as IlluminateCommand;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Class     Command
 *
 * @package  Arcanedev\LogViewer\Bases
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class Command extends IlluminateCommand
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogViewerInterface */
    protected $logViewer;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create the command instance.
     *
     * @param  LogViewerInterface  $logViewer
     */
    public function __construct(LogViewerInterface $logViewer)
    {
        parent::__construct();

        $this->logViewer = $logViewer;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Execute the console command.
     */
    abstract public function handle();

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Display LogViewer Logo and Copyrights.
     */
    protected function displayLogViewer()
    {
        $this->comment('   __                   _                        ');
        $this->comment('  / /  ___   __ _/\   /(_) _____      _____ _ __ ');
        $this->comment(' / /  / _ \ / _` \ \ / / |/ _ \ \ /\ / / _ \ \'__|');
        $this->comment('/ /__| (_) | (_| |\ V /| |  __/\ V  V /  __/ |   ');
        $this->comment('\____/\___/ \__, | \_/ |_|\___| \_/\_/ \___|_|   ');
        $this->comment('            |___/                                ');
        $this->line('');
        $this->comment('Version ' . LogViewer::VERSION . ' - Created by ARCANEDEV' . chr(169));
        $this->line('');
    }

    /**
     * Get table separator
     *
     * @return TableSeparator
     */
    protected function getTableSeparator()
    {
        return new TableSeparator;
    }

    /**
     * Display header
     *
     * @param  string  $header
     */
    protected function header($header)
    {
        $line   = '+' . str_repeat('-', strlen($header) + 4) . '+';

        $this->info($line);
        $this->info("|  $header  |");
        $this->info($line);
    }
}
