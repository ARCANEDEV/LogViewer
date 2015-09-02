<?php namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Utilities\LogParser;
use Arcanedev\Support\Collection;

/**
 * Class EntryCollection
 * @package Arcanedev\LogViewer\Entities
 */
class EntryCollection extends Collection
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
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
    }

}
