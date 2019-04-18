<?php namespace Arcanedev\LogViewer;

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Arcanedev\LogViewer\Contracts\Utilities\Factory as FactoryContract;
use Arcanedev\LogViewer\Contracts\Utilities\LogLevels as LogLevelsContract;
use Arcanedev\LogViewer\Contracts\LogViewer as LogViewerContract;

/**
 * Class     LogViewer
 *
 * @package  Arcanedev\LogViewer
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewer implements LogViewerContract
{
    /* -----------------------------------------------------------------
     |  Constants
     | -----------------------------------------------------------------
     */

    /**
     * LogViewer Version
     */
    const VERSION = '4.7.1';

    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The factory instance.
     *
     * @var \Arcanedev\LogViewer\Contracts\Utilities\Factory
     */
    protected $factory;

    /**
     * The filesystem instance.
     *
     * @var \Arcanedev\LogViewer\Contracts\Utilities\Filesystem
     */
    protected $filesystem;

    /**
     * The log levels instance.
     *
     * @var \Arcanedev\LogViewer\Contracts\Utilities\LogLevels
     */
    protected $levels;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Create a new instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\Factory     $factory
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\Filesystem  $filesystem
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\LogLevels   $levels
     */
    public function __construct(
        FactoryContract    $factory,
        FilesystemContract $filesystem,
        LogLevelsContract  $levels
    ) {
        $this->factory    = $factory;
        $this->filesystem = $filesystem;
        $this->levels     = $levels;
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the log levels.
     *
     * @param  bool  $flip
     *
     * @return array
     */
    public function levels($flip = false)
    {
        return $this->levels->lists($flip);
    }

    /**
     * Get the translated log levels.
     *
     * @param  string|null  $locale
     *
     * @return array
     */
    public function levelsNames($locale = null)
    {
        return $this->levels->names($locale);
    }

    /**
     * Set the log storage path.
     *
     * @param  string  $path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->factory->setPath($path);

        return $this;
    }

    /**
     * Get the log pattern.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->factory->getPattern();
    }

    /**
     * Set the log pattern.
     *
     * @param  string  $date
     * @param  string  $prefix
     * @param  string  $extension
     *
     * @return self
     */
    public function setPattern(
        $prefix    = FilesystemContract::PATTERN_PREFIX,
        $date      = FilesystemContract::PATTERN_DATE,
        $extension = FilesystemContract::PATTERN_EXTENSION
    ) {
        $this->factory->setPattern($prefix, $date, $extension);

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get all logs.
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function all()
    {
        return $this->factory->all();
    }

    /**
     * Paginate all logs.
     *
     * @param  int  $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 30)
    {
        return $this->factory->paginate($perPage);
    }

    /**
     * Get a log.
     *
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function get($date)
    {
        return $this->factory->log($date);
    }

    /**
     * Get the log entries.
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function entries($date, $level = 'all')
    {
        return $this->factory->entries($date, $level);
    }

    /**
     * Download a log file.
     *
     * @param  string       $date
     * @param  string|null  $filename
     * @param  array        $headers
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($date, $filename = null, $headers = [])
    {
        if (is_null($filename)) {
            $filename = "laravel-{$date}.log";
        }

        $path = $this->filesystem->path($date);

        return response()->download($path, $filename, $headers);
    }

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats()
    {
        return $this->factory->stats();
    }

    /**
     * Get logs statistics table.
     *
     * @param  string|null  $locale
     *
     * @return \Arcanedev\LogViewer\Tables\StatsTable
     */
    public function statsTable($locale = null)
    {
        return $this->factory->statsTable($locale);
    }

    /**
     * Delete the log.
     *
     * @param  string  $date
     *
     * @return bool
     */
    public function delete($date)
    {
        return $this->filesystem->delete($date);
    }

    /**
     * Clear the log files.
     *
     * @return bool
     */
    public function clear()
    {
        return $this->filesystem->clear();
    }

    /**
     * Get all valid log files.
     *
     * @return array
     */
    public function files()
    {
        return $this->filesystem->logs();
    }

    /**
     * List the log files (only dates).
     *
     * @return array
     */
    public function dates()
    {
        return $this->factory->dates();
    }

    /**
     * Get logs count.
     *
     * @return int
     */
    public function count()
    {
        return $this->factory->count();
    }

    /**
     * Get entries total from all logs.
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
     * Get logs tree.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function tree($trans = false)
    {
        return $this->factory->tree($trans);
    }

    /**
     * Get logs menu.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function menu($trans = true)
    {
        return $this->factory->menu($trans);
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Determine if the log folder is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->factory->isEmpty();
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the LogViewer version.
     *
     * @return string
     */
    public function version()
    {
        return self::VERSION;
    }
}
