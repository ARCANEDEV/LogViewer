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
