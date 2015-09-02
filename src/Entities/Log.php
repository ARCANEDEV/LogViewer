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

    /** @var EntryCollection */
    private $entries;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @param  string  $date
     * @param  string  $raw
     */
    public function __construct($date, $raw)
    {
        $this->entries = new EntryCollection;
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
     * @return EntryCollection
     */
    public function entries($level = 'all')
    {
        return $level == 'all'
            ? $this->entries
            : $this->filterByLevel($level);
    }

    /**
     * Get filtered log entries by level
     *
     * @param  string  $level
     *
     * @return EntryCollection
     */
    public function filterByLevel($level)
    {
        return $this->entries->filter(function(LogEntry $entry) use ($level) {
            return $entry->level == $level;
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
        return $this->entries
            ->groupBy('level')
            ->map(function(EntryCollection $entries, $key) use ($trans) {
                return [
                    'name'  => $trans ? trans('log-viewer::levels.' . $key) : $key,
                    'count' => $entries->count()
                ];
            })
            ->toArray();
    }
}
