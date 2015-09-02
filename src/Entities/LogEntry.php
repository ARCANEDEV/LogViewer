<?php namespace Arcanedev\LogViewer\Entities;

use Carbon\Carbon;

/**
 * Class LogEntry
 * @package Arcanedev\LogViewer\Entities
 */
class LogEntry
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var string */
    public $level;

    /** @var Carbon */
    public $datetime;

    /** @var string */
    public $header;

    /** @var string */
    public $stack;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @param  string  $level
     * @param  string  $header
     * @param  string  $stack
     */
    public function __construct($level, $header, $stack)
    {
        $this->setLevel($level);
        $this->setHeader($header);
        $this->stack  = $stack;
    }

    /**
     * @param  string  $level
     *
     * @return self
     */
    private function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Set header
     *
     * @param  string  $header
     *
     * @return self
     */
    private function setHeader($header)
    {
        $this->header = $header;

        $datetime = $this->extractDatetime($header);
        $this->setDatetime($datetime);

        return $this;
    }

    /**
     * Set date time
     *
     * @param  Carbon  $datetime
     *
     * @return self
     */
    private function setDatetime(Carbon $datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Extract datetime from header
     *
     * @param  string  $header
     *
     * @return Carbon
     */
    public function extractDatetime($header)
    {
        preg_match('/' . REGEX_DATETIME_PATTERN . '/', $header, $matches);

        return Carbon::createFromFormat('Y-m-d H:i:s', $matches[0]);
    }
}
