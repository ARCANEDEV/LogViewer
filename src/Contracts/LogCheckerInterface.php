<?php namespace Arcanedev\LogViewer\Contracts;

/**
 * Interface  LogCheckerInterface
 *
 * @package   Arcanedev\LogViewer\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface LogCheckerInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get messages.
     *
     * @return array
     */
    public function messages();

    /**
     * Check passes ??
     *
     * @return bool
     */
    public function passes();

    /**
     * Check fails ??
     *
     * @return bool
     */
    public function fails();

    /**
     * Get the requirements
     *
     * @return array
     */
    public function requirements();
}
