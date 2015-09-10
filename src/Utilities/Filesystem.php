<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Exceptions\FilesystemException;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;

/**
 * Class     Filesystem
 *
 * @package  Arcanedev\LogViewer\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
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
    protected $storagePath;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new instance.
     *
     * @param  IlluminateFilesystem  $files
     * @param  string                $storagePath
     */
    public function __construct(IlluminateFilesystem $files, $storagePath)
    {
        $this->filesystem  = $files;
        $this->storagePath = $storagePath;
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
     * Get all log files.
     *
     * @return array
     */
    public function all()
    {
        return $this->getFiles('*');
    }

    /**
     * Get all valid log files.
     *
     * @return array
     */
    public function logs()
    {
        $date = '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]'; // TODO: Refactor this regex

        return $this->getFiles('laravel-' . $date);
    }

    /**
     * List the log files (Only dates).
     *
     * @param  bool|false  $withPaths
     *
     * @return array
     */
    public function dates($withPaths = false)
    {
        $files = array_reverse($this->logs());
        $dates = $this->extractDates($files);

        if ($withPaths) {
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
     * @throws FilesystemException
     */
    public function read($date)
    {
        try {
            $path = $this->getLogPath($date);

            return $this->filesystem->get($path);
        }
        catch (\Exception $e) {
            throw new FilesystemException($e->getMessage());
        }
    }

    /**
     * Delete the log.
     *
     * @param  string  $date
     *
     * @return bool
     *
     * @throws FilesystemException
     */
    public function delete($date)
    {
        $path = $this->getLogPath($date);

        // @codeCoverageIgnoreStart
        if ( ! $this->filesystem->delete($path)) {
            throw new FilesystemException(
                'There was an error deleting the log.'
            );
        }
        // @codeCoverageIgnoreEnd

        return true;
    }

    /**
     * Get the log file path.
     *
     * @param  string  $date
     *
     * @return string
     *
     * @throws FilesystemException
     */
    public function path($date)
    {
        return $this->getLogPath($date);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all files.
     *
     * @param  string $pattern
     * @param  string $extension
     *
     * @return array
     */
    private function getFiles($pattern, $extension = '.log')
    {
        $pattern = $this->storagePath . DS . $pattern . $extension;
        $files   = array_map('realpath', glob($pattern, GLOB_BRACE));

        return array_filter($files);
    }

    /**
     * Get the log file path.
     *
     * @param  string  $date
     *
     * @return string
     *
     * @throws FilesystemException
     */
    private function getLogPath($date)
    {
        $path = "{$this->storagePath}/laravel-{$date}.log";

        if ( ! $this->filesystem->exists($path)) {
            throw new FilesystemException(
                'The log(s) could not be located at : ' . $path
            );
        }

        return realpath($path);
    }

    /**
     * Extract dates from files.
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
