<?php namespace Arcanedev\LogViewer\Contracts;

/**
 * Interface  LogStylerInterface
 *
 * @package   Arcanedev\LogViewer\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface LogStylerInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make level icon.
     *
     * @param  string       $level
     * @param  string|null  $default
     *
     * @return string
     */
    public function icon($level, $default = null);

    /**
     * Get level color.
     *
     * @param  string       $level
     * @param  string|null  $default
     *
     * @return string
     */
    public function color($level, $default = null);
}
