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
     * @var array
     */
    protected static $levels = [];

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log levels.
     *
     * @return array
     */
    public function lists()
    {
        return self::all();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get PSR log levels
     *
     * @return array
     */
    public static function all()
    {
        if (empty(self::$levels)) {
            $class        = new ReflectionClass(new LogLevel);
            self::$levels = $class->getConstants();
        }

        return self::$levels;
    }
}
