<?php namespace Arcanedev\LogViewer;

use Arcanedev\LogViewer\Contracts\LogViewerInterface;
use Arcanedev\LogViewer\Entities\EntryCollection;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogLevels;
use Arcanedev\LogViewer\Utilities\Factory;
use Arcanedev\LogViewer\Utilities\Filesystem;

/**
 * Class LogViewer
 * @package Arcanedev\LogViewer
 */
class LogViewer implements LogViewerInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The factory instance.
     *
     * @var \Arcanedev\LogViewer\Utilities\Factory
     */
    protected $factory;

    /**
     * The filesystem instance.
     *
     * @var \Arcanedev\LogViewer\Utilities\Filesystem
     */
    protected $filesystem;

    /**
     * The data instance.
     *
     * @var \Arcanedev\LogViewer\Entities\LogLevels
     */
    protected $logLevels;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new instance.
     *
     * @param  \Arcanedev\LogViewer\Utilities\Factory     $factory
     * @param  \Arcanedev\LogViewer\Utilities\Filesystem  $filesystem
     * @param  \Arcanedev\LogViewer\Entities\LogLevels    $data
     */
    public function __construct(Factory $factory, Filesystem $filesystem, LogLevels $data)
    {
        $this->factory    = $factory;
        $this->filesystem = $filesystem;
        $this->logLevels  = $data;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log levels.
     *
     * @return array
     */
    public function levels()
    {
        return $this->logLevels->lists();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all logs
     *
     * @return LogCollection
     */
    public function all()
    {
        return $this->factory->all();
    }

    /**
     * Get the log
     *
     * @param  string  $date
     *
     * @return Log
     */
    public function get($date)
    {
        return $this->factory->log($date);
    }

    /**
     * Get the log entries
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return EntryCollection
     */
    public function entries($date, $level = 'all')
    {
        return $this->factory->entries($date, $level);
    }

    /**
     * Delete the log.
     *
     * @param  string  $date
     *
     * @return bool
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function delete($date)
    {
        return $this->filesystem->delete($date);
    }

    /**
     * List the log files (dates).
     *
     * @return array
     */
    public function dates()
    {
        return $this->factory->dates();
    }

    /**
     * Get logs count
     *
     * @return int
     */
    public function count()
    {
        return $this->factory->count();
    }

    /**
     * Get total log entries
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total($level = 'all')
    {
        return $this->factory->total($level);
    }
}
