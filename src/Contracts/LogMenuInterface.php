<?php namespace Arcanedev\LogViewer\Contracts;

use Arcanedev\LogViewer\Entities\Log;

/**
 * Interface  LogMenuInterface
 *
 * @package   Arcanedev\LogViewer\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface LogMenuInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the log styler instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\LogStylerInterface  $styler
     *
     * @return self
     */
    public function setLogStyler(LogStylerInterface $styler);

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make log menu.
     *
     * @param  Log   $log
     * @param  bool  $trans
     *
     * @return array
     */
    public function make(Log $log, $trans = true);
}
