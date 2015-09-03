<?php namespace Arcanedev\LogViewer\Contracts;

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
