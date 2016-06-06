<?php namespace Arcanedev\LogViewer\Contracts;

/**
 * Interface  FactoryInterface
 *
 * @package   Arcanedev\LogViewer\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface FactoryInterface extends Patternable
{
    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the filesystem instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\FilesystemInterface
     */
    public function getFilesystem();

    /**
     * Set the filesystem instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\FilesystemInterface  $filesystem
     *
     * @return self
     */
    public function setFilesystem(FilesystemInterface $filesystem);

    /**
     * Get the log levels instance.
     *
     * @return  \Arcanedev\LogViewer\Contracts\LogLevelsInterface  $levels
     */
    public function getLevels();

    /**
     * Set the log levels instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\LogLevelsInterface  $levels
     *
     * @return self
     */
    public function setLevels(LogLevelsInterface $levels);

    /**
     * Set the log storage path.
     *
     * @param  string  $storagePath
     *
     * @return self
     */
    public function setPath($storagePath);

    /**
     * Get all logs.
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function logs();

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all logs (alias).
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function all();

    /**
     * Paginate all logs.
     *
     * @param  int  $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 30);

    /**
     * Get a log by date.
     *
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function log($date);

    /**
     * Get a log by date (alias).
     *
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function get($date);

    /**
     * Get log entries.
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
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
     * @return \Arcanedev\LogViewer\Tables\StatsTable
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
