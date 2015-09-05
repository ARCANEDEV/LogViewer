<?php namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Utilities\LogParser;
use Arcanedev\Support\Collection;

/**
 * Class     LogEntryCollection
 *
 * @package  Arcanedev\LogViewer\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryCollection extends Collection
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Load raw log entries
     *
     * @param  string  $raw
     *
     * @return self
     */
    public function load($raw)
    {
        foreach (LogParser::parse($raw) as $entry) {
            list($level, $header, $stack) = array_values($entry);

            $this->push(new LogEntry($level, $header, $stack));
        }

        return $this;
    }

    /**
     * Get filtered log entries by level
     *
     * @param  string  $level
     *
     * @return LogEntryCollection
     */
    public function filterByLevel($level)
    {
        return $this->filter(function(LogEntry $entry) use ($level) {
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

        $this->groupBy('level')
            ->each(function (LogEntryCollection $entries, $level) use (&$counters) {
                $counters[$level] = $count = $entries->count();
                $counters['all'] += $count;
            });

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
                'name'  => $trans ? trans("log-viewer::levels.$level") : $level,
                'count' => $count,
            ];
        });

        return $tree;
    }

    /**
     * Get log entries menu.
     *
     * @param  bool|true  $trans
     *
     * @return array
     */
    public function menu($trans = true)
    {
        // TODO: Add the icons and stuff
        return $this->tree($trans);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
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
