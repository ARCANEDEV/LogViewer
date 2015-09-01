<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Entities\Log;

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
     * The filesystem instance.
     *
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * The log levels.
     *
     * @var array
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
     * @param  string[]             $levels
     */
    public function __construct(FilesystemInterface $filesystem, array $levels)
    {
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
     * Get the filesystem instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\FilesystemInterface  $filesystem
     *
     * @return self
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Set log levels
     *
     * @param  string[]  $levels
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
     * Get the log instance.
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function make($date, $level = 'all')
    {
        return new Log(
            $this->getFilesystem()->read($date),
            $this->levels,
            $level
        );
    }
}
