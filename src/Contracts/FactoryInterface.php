<?php namespace Arcanedev\LogViewer\Contracts;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Tables\StatsTable;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface  FactoryInterface
 *
 * @package   Arcanedev\LogViewer\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface FactoryInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all logs.
     *
     * @return LogCollection
     */
    public function logs();

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all logs (alias).
     *
     * @return LogCollection
     */
    public function all();

    /**
     * Paginate all logs.
     *
     * @param  int  $perPage
     *
     * @return LengthAwarePaginator
     */
    public function paginate($perPage = 30);

    /**
     * Get a log by date.
     *
     * @param  string  $date
     *
     * @return Log
     */
    public function log($date);

    /**
     * Get a log by date (alias).
     *
     * @param  string  $date
     *
     * @return Log
     */
    public function get($date);

    /**
     * Get log entries.
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
     * Get logs count.
     *
     * @return int
     */
    public function count();

    /**
     * Get total log entries.
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total($level = 'all');

    /**
     * Get tree menu.
     *
     * @param  bool|false  $trans
     *
     * @return array
     */
    public function tree($trans = false);

    /**
     * Get tree menu.
     *
     * @param  bool|true  $trans
     *
     * @return array
     */
    public function menu($trans = true);

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats();

    /**
     * Get logs statistics table.
     *
     * @param  string|null  $locale
     *
     * @return StatsTable
     */
    public function statsTable($locale = null);

    /* ------------------------------------------------------------------------------------------------
     |  Check Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Determine if the log folder is empty or not.
     *
     * @return bool
     */
    public function isEmpty();
}
