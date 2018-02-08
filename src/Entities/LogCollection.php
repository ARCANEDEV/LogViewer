<?php namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Arcanedev\LogViewer\Exceptions\LogNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class     LogCollection
 *
 * @package  Arcanedev\LogViewer\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogCollection extends Collection
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var \Arcanedev\LogViewer\Contracts\Utilities\Filesystem */
    private $filesystem;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * LogCollection constructor.
     *
     * @param  array  $items
     */
    public function __construct($items = [])
    {
        $this->setFilesystem(app(FilesystemContract::class));

        parent::__construct($items);

        if (empty($items)) $this->load();
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Set the filesystem instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\Filesystem  $filesystem
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    public function setFilesystem(FilesystemContract $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Load all logs.
     *
     * @return \Arcanedev\LogViewer\Entities\LogCollection
     */
    private function load()
    {
        foreach($this->filesystem->dates(true) as $date => $path) {
            $this->put($date, Log::make($date, $path, $this->filesystem->read($date)));
        }

        return $this;
    }

    /**
     * Get a log.
     *
     * @param  string      $date
     * @param  mixed|null  $default
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     *
     * @throws \Arcanedev\LogViewer\Exceptions\LogNotFoundException
     */
    public function get($date, $default = null)
    {
        if ( ! $this->has($date))
            throw new LogNotFoundException("Log not found in this date [$date]");

        return parent::get($date, $default);
    }

    /**
     * Paginate logs.
     *
     * @param  int  $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 30)
    {
        $page = request()->get('page', 1);
        $path = request()->url();

        return new LengthAwarePaginator(
            $this->forPage($page, $perPage),
            $this->count(),
            $perPage,
            $page,
            compact('path')
        );
    }

    /**
     * Get a log (alias).
     *
     * @see get()
     *
     * @param  string  $date
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function log($date)
    {
        return $this->get($date);
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
        return $this->get($date)->entries($level);
    }

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats()
    {
        $stats = [];

        foreach ($this->items as $date => $log) {
            /** @var \Arcanedev\LogViewer\Entities\Log $log */
            $stats[$date] = $log->stats();
        }

        return $stats;
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
     * Get entries total.
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
     * Get logs tree.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function tree($trans = false)
    {
        $tree = [];

        foreach ($this->items as $date => $log) {
            /** @var \Arcanedev\LogViewer\Entities\Log $log */
            $tree[$date] = $log->tree($trans);
        }

        return $tree;
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
        $menu = [];

        foreach ($this->items as $date => $log) {
            /** @var \Arcanedev\LogViewer\Entities\Log $log */
            $menu[$date] = $log->menu($trans);
        }

        return $menu;
    }
}
