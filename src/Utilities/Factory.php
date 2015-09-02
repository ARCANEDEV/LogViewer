<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Entities\EntryCollection;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;

/**
 * Class Factory
 * @package Arcanedev\LogViewer\Log
 */
class Factory
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @var LogCollection
     */
    protected $logs;

    /**
     * The filesystem instance.
     *
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * The log levels.
     *
     * @var array
     *
     * @TODO: Keep it or remove it ??
     */
    protected $levels;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new instance.
     *
     * @param  FilesystemInterface  $filesystem
     * @param  array                $levels
     */
    public function __construct(FilesystemInterface $filesystem, array $levels)
    {
        $this->logs = new LogCollection;
        $this->setFilesystem($filesystem);
        $this->setLevels($levels);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getter & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the filesystem instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\FilesystemInterface
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Set the filesystem instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\FilesystemInterface  $filesystem
     *
     * @return self
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->logs->setFilesystem($filesystem);
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Set log levels
     *
     * @param  array  $levels
     *
     * @return self
     */
    public function setLevels(array $levels)
    {
        $this->levels = $levels;

        return $this;
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
    public function logs()
    {
        return $this->logs->load();
    }

    /**
     * Get all logs (alias)
     *
     * @return LogCollection
     */
    public function all()
    {
        return $this->logs();
    }

    /**
     * Get a log by date
     *
     * @param  string  $date
     *
     * @return Log
     */
    public function log($date)
    {
        return $this->logs()->log($date);
    }

    /**
     * Get a log by date (alias)
     *
     * @param  string  $date
     *
     * @return Log
     */
    public function get($date)
    {
        return $this->log($date);
    }

    /**
     * Get log entries
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return EntryCollection
     */
    public function entries($date, $level = 'all')
    {
        return $this->logs()->entries($date, $level);
    }

    /**
     * List the log files (dates).
     *
     * @return array
     */
    public function dates()
    {
        return $this->logs()->dates();
    }

    /**
     * Get logs count
     *
     * @return int
     */
    public function count()
    {
        return $this->logs()->count();
    }

    /**
     * Get total log entries
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total($level)
    {
        return $this->logs()->total($level);
    }
}
