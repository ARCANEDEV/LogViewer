<?php namespace Arcanedev\LogViewer\Contracts;

use Arcanedev\LogViewer\Exceptions\FilesystemException;

/**
 * Interface  FilesystemInterface
 *
 * @package   Arcanedev\LogViewer\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface FilesystemInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the files instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getInstance();

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all log files.
     *
     * @return array
     */
    public function all();

    /**
     * Get all valid log files.
     *
     * @return array
     */
    public function logs();

    /**
     * List the log files (Only dates).
     *
     * @param  bool|false  $withPaths
     *
     * @return array
     */
    public function dates($withPaths = false);

    /**
     * Read the log.
     *
     * @param  string  $date
     *
     * @return string
     *
     * @throws FilesystemException
     */
    public function read($date);

    /**
     * Delete the log.
     *
     * @param  string  $date
     *
     * @return bool
     *
     * @throws FilesystemException
     */
    public function delete($date);

    /**
     * Get the log file path.
     *
     * @param  string  $date
     *
     * @return string
     *
     * @throws FilesystemException
     */
    public function path($date);
}
