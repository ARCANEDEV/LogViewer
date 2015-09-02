<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Exceptions\FilesystemException;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;

/**
 * Class Filesystem
 * @package Arcanedev\LogViewer\Log
 */
class Filesystem implements FilesystemInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The filesystem instance.
     *
     * @var IlluminateFilesystem
     */
    protected $filesystem;

    /**
     * The base storage path.
     *
     * @var string
     */
    protected $path;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new instance.
     *
     * @param  IlluminateFilesystem  $files
     * @param  string                $path
     */
    public function __construct(IlluminateFilesystem $files, $path)
    {
        $this->filesystem = $files;
        $this->path       = $path;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the files instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getInstance()
    {
        return $this->filesystem;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * List the log files.
     *
     * @return array
     */
    public function files()
    {
        return glob($this->path . '/laravel-*.log', GLOB_BRACE);
    }

    /**
     * Get list files
     *
     * @param  bool|false  $withPath
     *
     * @return array
     */
    public function dates($withPath = false)
    {
        $files = array_reverse($this->files());
        $dates = $this->extractDates($files);

        if ($withPath) {
            $dates = array_combine($dates, $files); // [date => file]
        }

        return $dates;
    }

    /**
     * Read the log.
     *
     * @param  string  $date
     *
     * @return string
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function read($date)
    {
        try {
            return $this->filesystem->get($this->path($date));
        }
        catch (\Exception $e) {
            throw new FilesystemException($e->getMessage());
        }
    }

    /**
     * Delete the log.
     *
     * @param  string $date
     *
     * @return bool
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function delete($date)
    {
        $success = $this->filesystem->delete($this->path($date));

        // @codeCoverageIgnoreStart
        if ( ! $success) {
            throw new FilesystemException(
                'There was an error deleting the log.'
            );
        }
        // @codeCoverageIgnoreEnd

        return $success;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log file path.
     *
     * @param  string $date
     *
     * @return string
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    private function path($date)
    {
        $path = "{$this->path}/laravel-{$date}.log";

        if ( ! $this->filesystem->exists($path)) {
            throw new FilesystemException(
                'The log(s) could not be located at : ' . $path
            );
        }

        return realpath($path);
    }

    /**
     * Extract dates from files
     *
     * @param  array  $files
     *
     * @return array
     */
    private function extractDates(array $files)
    {
        return array_map(function ($file) {
            return extract_date(basename($file));
        }, $files);
    }
}
