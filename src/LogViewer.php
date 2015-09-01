<?php namespace Arcanedev\LogViewer;

use Arcanedev\LogViewer\Utilities\Factory;
use Arcanedev\LogViewer\Utilities\Filesystem;
use Arcanedev\LogViewer\Entities\LogLevels;

/**
 * Class LogViewer
 * @package Arcanedev\LogViewer
 */
class LogViewer
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
     |  Getter & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the factory instance.
     *
     * @return \Arcanedev\LogViewer\Utilities\Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Arcanedev\LogViewer\Utilities\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Get the data instance.
     *
     * @return \Arcanedev\LogViewer\Entities\LogLevels
     */
    public function getLogLevels()
    {
        return $this->logLevels;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log data.
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return array
     */
    public function read($date, $level = 'all')
    {
        return $this->factory->make($date, $level)->entries();
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
     * List the log files.
     *
     * @return string[]
     */
    public function logs()
    {
        $logs = array_reverse($this->filesystem->files());

        foreach ($logs as $index => $file) {
            $logs[$index] = preg_replace('/.*(\d{4}-\d{2}-\d{2}).*/', '$1', basename($file));
        }

        return $logs;
    }

    /**
     * Get the log levels.
     *
     * @return string[]
     */
    public function levels()
    {
        return $this->logLevels->all();
    }
}
