<?php namespace Arcanedev\LogViewer;

use Arcanedev\LogViewer\Contracts\FactoryInterface;
use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Contracts\LogLevelsInterface;
use Arcanedev\LogViewer\Contracts\LogViewerInterface;

/**
 * Class     LogViewer
 *
 * @package  Arcanedev\LogViewer
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewer implements LogViewerInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Constants
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * LogViewer Version
     */
    const VERSION = '3.9.0';

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
     * The log levels instance.
     *
     * @var LogLevelsInterface
     */
    protected $levels;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new instance.
     *
     * @param  FactoryInterface     $factory
     * @param  FilesystemInterface  $filesystem
     * @param  LogLevelsInterface   $levels
     */
    public function __construct(
        FactoryInterface    $factory,
        FilesystemInterface $filesystem,
        LogLevelsInterface  $levels
    ) {
        $this->factory    = $factory;
        $this->filesystem = $filesystem;
        $this->levels     = $levels;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log levels.
     *
     * @param  bool|false  $flip
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
     * @return \Arcanedev\LogViewer\LogViewer
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
        $prefix    = FilesystemInterface::PATTERN_PREFIX,
        $date      = FilesystemInterface::PATTERN_DATE,
        $extension = FilesystemInterface::PATTERN_EXTENSION
    ) {
        $this->factory->setPattern($prefix, $date, $extension);

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all logs.
     *
     * @return Entities\LogCollection
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
     * @return Entities\Log
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
     * @return Entities\LogEntryCollection
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
     * @return Tables\StatsTable
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
     *
     * @throws Exceptions\FilesystemException
     */
    public function delete($date)
    {
        return $this->filesystem->delete($date);
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
     * @param  bool|false  $trans
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
     * @param  bool|true  $trans
     *
     * @return array
     */
    public function menu($trans = true)
    {
        return $this->factory->menu($trans);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Check Functions
     | ------------------------------------------------------------------------------------------------
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

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
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
