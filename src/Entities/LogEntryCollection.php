<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Contracts\LogEntryInterface;
use Arcanedev\LogViewer\Format\DriverRegistry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

/**
 * Class     LogEntryCollection
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryCollection extends LazyCollection
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Load raw log entries.
     *
     * @param  string  $raw
     *
     * @return self
     */
    public static function load($raw)
    {
        $driver = static::resolveDriver();

        return new static(function () use ($raw, $driver) {
            yield from $driver->parse($raw);
        });
    }

    /**
     * Resolve the format driver behind the read seam.
     *
     * Uses the container-bound registry when available (so host apps can
     * register custom drivers), otherwise falls back to a fresh registry —
     * keeping this usable outside a fully booted container.
     *
     * @return \Arcanedev\LogViewer\Contracts\FormatDriver
     */
    protected static function resolveDriver()
    {
        $registry = (function_exists('app') && app()->bound(DriverRegistry::class))
            ? app(DriverRegistry::class)
            : new DriverRegistry;

        return $registry->driver();
    }

    /**
     * Paginate log entries.
     *
     * @param  int  $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 20)
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
     * Get filtered log entries by level.
     *
     * @param  string  $level
     *
     * @return self
     */
    public function filterByLevel($level)
    {
        return $this->filter(function(LogEntryInterface $entry) use ($level) {
            return $entry->isSameLevel($level);
        });
    }

    /**
     * Get log entries stats.
     *
     * @return array
     */
    public function stats()
    {
        $counters = $this->initStats();

        foreach ($this->groupBy('level') as $level => $entries) {
            $counters[$level] = $count = count($entries);
            $counters['all'] += $count;
        }

        return $counters;
    }

    /**
     * Get the log entries navigation tree.
     *
     * @param  bool|false  $trans
     *
     * @return array
     */
    public function tree($trans = false)
    {
        $tree = $this->stats();

        array_walk($tree, function(&$count, $level) use ($trans) {
            $count = [
                'name'  => $trans ? log_levels()->get($level) : $level,
                'count' => $count,
            ];
        });

        return $tree;
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Init stats counters.
     *
     * @return array
     */
    private function initStats()
    {
        $levels = array_merge_recursive(
            ['all'],
            array_keys(log_viewer()->levels(true))
        );

        return array_map(function () {
            return 0;
        }, array_flip($levels));
    }
}
