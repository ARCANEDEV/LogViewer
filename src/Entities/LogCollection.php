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
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the filesystem instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\FilesystemInterface  $filesystem
     *
     * @return self
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * List the log files (dates).
     *
     * @return array
     */
    public function dates()
    {
        $this->load();

        return $this->keys()->toArray();
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
    public function load()
    {
        foreach($this->getFiles() as $date => $file) {
            $this->put($date, new Log($date, $this->filesystem->read($date)));
        }

        return $this;
    }

    /**
     * Get log
     *
     * @param  string $date
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
     * @return EntryCollection|null
     */
    public function entries($date, $level)
    {
        return $this->log($date)->entries($level);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get list files [date => file]
     *
     * @return array
     */
    private function getFiles()
    {
        $files = array_reverse($this->filesystem->files());
        $dates = $this->extractDates($files);

        return array_combine($dates, $files);
    }

    /**
     * Extract dates from files
     *
     * @param  array $files
     *
     * @return array
     */
    private function extractDates(array $files)
    {
        return array_map(function ($file) {
            return preg_replace(
                '/.*(' . REGEX_DATE_PATTERN . ').*/',
                '$1',
                basename($file)
            );
        }, $files);
    }

    /**
     * @param  string $level
     *
     * @return int
     */
    public function total($level)
    {
        return (int) $this->sum(function (Log $log) use ($level) {
            return $log->entries($level)->count();
        });
    }

    /**
     * Get tree menu
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function tree($trans = false)
    {
        return $this->map(function (Log $log) use ($trans) {
            return $log->tree($trans);
        })->toArray();
    }
}
