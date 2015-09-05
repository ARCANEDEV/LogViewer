<?php namespace Arcanedev\LogViewer\Contracts;

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
     * Make menu item
     *
     * @param  string      $level
     * @param  int         $count
     * @param  bool|false  $translateName
     * @param  bool|false  $withIcon
     *
     * @return array
     */
    public function item($level, $count, $translateName = false, $withIcon = false);
}
