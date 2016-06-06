<?php namespace Arcanedev\LogViewer\Contracts;

/**
 * Interface  Patternable
 *
 * @package   Arcanedev\LogViewer\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface Patternable
{
    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters 
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log pattern.
     *
     * @return string
     */
    public function getPattern();

    /**
     * Set the log pattern.
     *
     * @param  string  $date
     * @param  string  $prefix
     * @param  string  $extension
     *
     * @return self
     */
    public function setPattern(
        $prefix    = FilesystemInterface::PATTERN_PREFIX,
        $date      = FilesystemInterface::PATTERN_DATE,
        $extension = FilesystemInterface::PATTERN_EXTENSION
    );
}
