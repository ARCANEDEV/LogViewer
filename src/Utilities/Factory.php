<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\FactoryInterface;
use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Contracts\LogLevelsInterface;
use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Tables\StatsTable;

/**
 * Class     Factory
 *
 * @package  Arcanedev\LogViewer\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
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

    /**
     * @var LogLevelsInterface
     */
    private $levels;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new instance.
     *
     * @param  FilesystemInterface  $filesystem
     * @param  LogLevelsInterface   $levels
     */
    public function __construct(
        FilesystemInterface $filesystem,
        LogLevelsInterface $levels
    ) {
        $this->setFilesystem($filesystem);
        $this->levels = $levels;
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

    /**
     * Get all logs.
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function logs()
    {
        return new LogCollection;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all logs. (alias)
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function all()
    {
        return $this->logs();
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
        return $this->logs()->paginate($perPage);
    }

    /**
     * Get a log by date.
     *
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function log($date)
    {
        return $this->logs()->log($date);
    }

    /**
     * Get a log by date (alias).
     *
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function get($date)
    {
        return $this->log($date);
    }

    /**
     * Get log entries.
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function entries($date, $level = 'all')
    {
        return $this->logs()->entries($date, $level);
    }

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats()
    {
        return $this->logs()->stats();
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
        return StatsTable::make($this->stats(), $this->levels, $locale);
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
     * Get logs count.
     *
     * @return int
     */
    public function count()
    {
        return $this->logs()->count();
    }

    /**
     * Get total log entries.
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total($level = 'all')
    {
        return $this->logs()->total($level);
    }

    /**
     * Get tree menu.
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
     * Get tree menu.
     *
     * @param  bool|true  $trans
     *
     * @return array
     */
    public function menu($trans = true)
    {
        return $this->logs()->menu($trans);
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
        return $this->logs()->isEmpty();
    }
}
