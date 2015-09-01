<?php namespace Arcanedev\LogViewer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class LogViewer
 * @package Arcanedev\LogViewer\Facades
 */
class LogViewer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'log-viewer'; }
}
