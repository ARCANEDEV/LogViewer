<?php namespace Arcanedev\LogViewer\Bases;

use Arcanedev\LogViewer\Contracts\LogViewerInterface;
use Illuminate\Console\Command as IlluminateCommand;

/**
 * Class Command
 * @package Arcanedev\LogViewer\Bases
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
}
