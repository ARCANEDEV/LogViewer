<?php namespace Arcanedev\LogViewer\Contracts;

/**
 * Interface LogLevelsInterface
 * @package Arcanedev\LogViewer\Contracts
 */
interface LogLevelsInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log levels.
     *
     * @return array
     */
    public function lists();

    /**
     * Get translated levels.
     *
     * @param  string|null  $locale
     *
     * @return array
     */
    public function names($locale = null);

    /**
     * Get PSR log levels.
     *
     * @return array
     */
    public static function all();
}
