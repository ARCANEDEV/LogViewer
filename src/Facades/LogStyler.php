<?php namespace Arcanedev\LogViewer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class     LogStyler
 *
 * @package  Arcanedev\LogViewer\Facades
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogStyler extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'arcanedev.log-viewer.styler'; }
}
