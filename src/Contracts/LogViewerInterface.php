<?php namespace Arcanedev\LogViewer\Contracts;

use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Exceptions\FilesystemException;

/**
 * Interface LogViewerInterface
 * @package Arcanedev\LogViewer\Contracts
 */
interface LogViewerInterface
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
    public function all();

    /**
     * Get the log
     *
     * @param  string  $date
     *
     * @return Log
     */
    public function get($date);

    /**
     * Get the log entries
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return LogEntryCollection
     */
    public function entries($date, $level = 'all');

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
     * List the log files (dates).
     *
     * @return array
     */
    public function dates();

    /**
     * Get the log levels.
     *
     * @return array
     */
    public function levels();

    /**
     * Get logs count
     *
     * @return int
     */
    public function count();

    /**
     * Get total log entries
     *
     * @return int
     */
    public function total();

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
     * Download a log file
     *
     * @param  string  $date
     * @param  string  $filename
     * @param  array   $headers
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($date, $filename = null, $headers = []);

    // TODO: Add pagination
}
