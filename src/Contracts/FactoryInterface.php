<?php namespace Arcanedev\LogViewer\Contracts;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogEntryCollection;

/**
 * Interface FactoryInterface
 * @package Arcanedev\LogViewer\Contracts
 */
interface FactoryInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all logs
     *
     * @return LogCollection
     */
    public function logs();

    /**
     * Get all logs (alias)
     *
     * @return LogCollection
     */
    public function all();

    /**
     * Get a log by date
     *
     * @param  string  $date
     *
     * @return Log
     */
    public function log($date);

    /**
     * Get a log by date (alias)
     *
     * @param  string  $date
     *
     * @return Log
     */
    public function get($date);

    /**
     * Get log entries
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return LogEntryCollection
     */
    public function entries($date, $level = 'all');

    /**
     * List the log files (dates).
     *
     * @return array
     */
    public function dates();

    /**
     * Get logs count
     *
     * @return int
     */
    public function count();

    /**
     * Get total log entries
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total($level);

    /**
     * Get tree menu
     *
     * @param  bool|false  $trans
     *
     * @return array
     */
    public function tree($trans = false);

    /**
     * Get tree menu
     *
     * @param  bool|true  $trans
     *
     * @return array
     */
    public function menu($trans = true);

    /**
     * Get logs statistics
     *
     * @return array
     */
    public function stats();
}
