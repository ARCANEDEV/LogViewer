<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\FactoryInterface;
use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;

/**
 * Class Factory
 * @package Arcanedev\LogViewer\Log
 */
class Factory implements FactoryInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The filesystem instance.
     *
     * @var FilesystemInterface
     */
    protected $filesystem;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new instance.
     *
     * @param  FilesystemInterface  $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->setFilesystem($filesystem);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getter & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the filesystem instance.
     *
     * @return FilesystemInterface
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Set the filesystem instance.
     *
     * @param  FilesystemInterface  $filesystem
     *
     * @return self
     */
    private function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;

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
        return new LogCollection;
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
     * @return LogEntryCollection
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

    /**
     * Get tree menu
     *
     * @param  bool|false  $trans
     *
     * @return array
     */
    public function tree($trans = false)
    {
        return $this->logs()->tree($trans);
    }

    /**
     * Get tree menu
     *
     * @param  bool|true  $trans
     *
     * @return array
     */
    public function menu($trans = true)
    {
        return $this->logs()->menu($trans);
    }
}
