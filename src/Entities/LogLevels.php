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
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log levels.
     *
     * @return string[]
     */
    public function all()
    {
        if (empty($this->levels)) {
            $this->levels = $this->getDefaultLevels();
        }

        return $this->levels;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get PSR log levels
     *
     * @return string[]
     */
    private function getDefaultLevels()
    {
        $class  = new ReflectionClass(new LogLevel);
        /** @var string[] $levels */
        $levels = $class->getConstants();

        return $levels;
    }
}
