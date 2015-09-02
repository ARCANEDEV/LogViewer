<?php namespace Arcanedev\LogViewer\Entities;

/**
 * Class Log
 * @package Arcanedev\LogViewer\Entities
 */
class Log
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var string */
    public $date;

    /** @var LogEntryCollection */
    private $entries;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Constructor
     *
     * @param  string  $date
     * @param  string  $raw
     */
    public function __construct($date, $raw)
    {
        $this->entries = new LogEntryCollection;
        $this->date    = $date;
        $this->entries->load($raw);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get log entries
     *
     * @param  string  $level
     *
     * @return LogEntryCollection
     */
    public function entries($level = 'all')
    {
        if ($level === 'all') {
            return $this->entries;
        }

        return $this->getByLevel($level);
    }

    /**
     * Get filtered log entries by level
     *
     * @param  string  $level
     *
     * @return LogEntryCollection
     */
    public function getByLevel($level)
    {
        return $this->entries->filter(function(LogEntry $entry) use ($level) {
            return $entry->level == $level;
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
        return $this->entries
            ->groupBy('level')
            ->map(function(LogEntryCollection $entries, $key) use ($trans) {
                return [
                    'name'  => $trans ? trans('log-viewer::levels.' . $key) : $key,
                    'count' => $entries->count()
                ];
            })
            ->toArray();
    }

    /**
     * Get tree menu (alias)
     *
     * @see    \Arcanedev\LogViewer\Entities\Log::tree()
     *
     * @param  bool|true  $trans
     *
     * @return array
     */
    public function menu($trans = true)
    {
        $tree = $this->tree($trans);

        return $tree;
    }
}
