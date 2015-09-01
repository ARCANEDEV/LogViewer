<?php namespace Arcanedev\LogViewer\Entities;

use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Class LogLevels
 * @package Arcanedev\LogViewer\Log
 */
class LogLevels
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The cached log levels.
     *
     * @var string[]
     */
    protected $levels = [];

    /* ------------------------------------------------------------------------------------------------
     |  Constructors
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log levels.
     *
     * @return string[]
     */
    public function all()
    {
        if ( ! $this->levels || empty($this->levels)) {
            $class        = new ReflectionClass(new LogLevel);
            $this->levels = $class->getConstants();
        }

        return $this->levels;
    }
}
