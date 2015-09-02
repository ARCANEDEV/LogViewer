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

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
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
        $this->setDatetime(extract_datetime($header));

        return $this;
    }

    /**
     * Set date time
     *
     * @param  string  $datetime
     *
     * @return self
     */
    private function setDatetime($datetime)
    {
        $this->datetime = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $datetime
        );

        return $this;
    }
}
