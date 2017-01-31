<?php namespace Arcanedev\LogViewer\Facades;

use Arcanedev\LogViewer\Contracts\LogViewer as LogViewerContract;
use Illuminate\Support\Facades\Facade;

/**
 * Class     LogViewer
 *
 * @package  Arcanedev\LogViewer\Facades
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return LogViewerContract::class; }
}
