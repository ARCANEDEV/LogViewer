<?php namespace Arcanedev\LogViewer\Contracts;

/**
 * Interface FilesystemInterface
 * @package Arcanedev\LogViewer\Contracts
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
     * List the log files.
     *
     * @return array
     */
    public function files();

    /**
     * Get list files
     *
     * @param  bool|false  $withPath
     *
     * @return array
     */
    public function dates($withPath = false);

    /**
     * Read the log.
     *
     * @param  string  $date
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     *
     * @return string
     */
    public function read($date);

    /**
     * Delete the log.
     *
     * @param  string  $date
     *
     * @return bool
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function delete($date);
}
