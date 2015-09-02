<?php namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Exceptions\LogNotFound;
use Arcanedev\Support\Collection;

/**
 * Class LogCollection
 * @package Arcanedev\LogViewer\Entities
 */
class LogCollection extends Collection
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var FilesystemInterface */
    private $filesystem;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Constructor
     *
     * @param  array  $items
     */
    public function __construct($items = [])
    {
        $this->setFilesystem(app('log-viewer.filesystem'));

        parent::__construct($items);

        if (empty($items)) {
            $this->load();
        }
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
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
     |  Main functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the filesystem instance.
     *
     * @return self
     */
    private function load()
    {
        foreach($this->filesystem->dates() as $date) {
            $log = new Log($date, $this->filesystem->read($date));

            $this->put($date, $log);
        }

        return $this;
    }

    /**
     * Get log
     *
     * @param  string  $date
     *
     * @return Log
     *
     * @throws LogNotFound
     */
    public function log($date)
    {
        if ( ! $this->has($date)) {
            throw new LogNotFound(
                'Log not found in this date [' .$date . ']'
            );
        }

        return $this->get($date);
    }

    /**
     * Get log entries
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return LogEntryCollection|null
     */
    public function entries($date, $level)
    {
        return $this->log($date)->entries($level);
    }

    /**
     * List the log files (dates).
     *
     * @return array
     */
    public function dates()
    {
        return $this->keys()->toArray();
    }

    /**
     * Get entries total
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total($level = 'all')
    {
        return (int) $this->sum(function (Log $log) use ($level) {
            return $log->entries($level)->count();
        });
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
        return $this->map(function (Log $log) use ($trans) {
            return $log->tree($trans);
        })->toArray();
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
        return $this->map(function (Log $log) use ($trans) {
            return $log->menu($trans);
        })->toArray();
    }
}
