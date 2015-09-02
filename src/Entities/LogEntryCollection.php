<?php namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Utilities\LogParser;
use Arcanedev\Support\Collection;

/**
 * Class LogEntryCollection
 * @package Arcanedev\LogViewer\Entities
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
}
