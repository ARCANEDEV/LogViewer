<?php namespace Arcanedev\LogViewer\Http\Routes;

use Arcanedev\Support\Laravel\RouteRegister;
use Illuminate\Contracts\Routing\Registrar;

/**
 * Class     LogViewerRoute
 *
 * @package  Arcanedev\LogViewer\Http\Routes
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerRoute extends RouteRegister
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function map(Registrar $router)
    {
        parent::map($router);

        $this->group([

        ], function () {
            // TODO:  Complete route registration
        });
    }
}
