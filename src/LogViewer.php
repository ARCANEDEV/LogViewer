<?php namespace Arcanedev\LogViewer;

use Arcanedev\LogViewer\Contracts\FactoryInterface;
use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Contracts\LogLevelsInterface;
use Arcanedev\LogViewer\Contracts\LogViewerInterface;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Entities\LogEntryCollection;

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
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * The filesystem instance.
     *
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * The data instance.
     *
     * @var LogLevelsInterface
     */
    protected $logLevels;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new instance.
     *
     * @param  FactoryInterface     $factory
     * @param  FilesystemInterface  $filesystem
     * @param  LogLevelsInterface   $data
     */
    public function __construct(
        FactoryInterface    $factory,
        FilesystemInterface $filesystem,
        LogLevelsInterface  $data
    ) {
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
     * @return LogEntryCollection
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

    /**
     * Get tree menu
     *
     * @param  bool|false  $trans
     *
     * @return array
     */
    public function tree($trans = false)
    {
        return $this->factory->tree($trans);
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
        return $this->factory->menu($trans);
    }

    /**
     * Download a log file
     *
     * @param  string  $date
     * @param  string  $filename
     * @param  array   $headers
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($date, $filename = null, $headers = [])
    {
        $path = $this->filesystem->path($date);

        if (is_null($filename)) {
            $filename = "laravel-{$date}.log";
        }

        return response()->download($path, $filename, $headers);
    }
}
