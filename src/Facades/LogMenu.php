<?php namespace Arcanedev\LogViewer\Facades;

use Arcanedev\LogViewer\Contracts\Utilities\LogMenu as LogMenuContract;
use Illuminate\Support\Facades\Facade;

/**
 * Class     LogMenu
 *
 * @package  Arcanedev\LogViewer\Facades
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogMenu extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return LogMenuContract::class; }
}
