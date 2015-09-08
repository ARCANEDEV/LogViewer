<?php namespace Arcanedev\LogViewer\Http\Routes;

use Arcanedev\Support\Laravel\RouteRegister;
use Illuminate\Contracts\Routing\Registrar;

/**
 * Class     LogViewerRoute
 *
 * @package  Arcanedev\LogViewer\Http\Routes
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @codeCoverageIgnore
 */
class LogViewerRoute extends RouteRegister
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Map all routes.
     *
     * @param  Registrar  $router
     */
    public function map(Registrar $router)
    {
        parent::map($router);

        $this->get('/', [
            'as'    => 'dashboard',
            'uses'  => 'LogViewerController@index',
        ]);

        $this->group([
            'as'     => 'logs.',
            'prefix' => 'logs/{date}',
        ], function() {
            $this->registerLogsRoutes();
        });
    }

    /* ------------------------------------------------------------------------------------------------
     |  Route Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register logs routes.
     */
    private function registerLogsRoutes()
    {
        $this->get('/', [
            'as'    => 'show',
            'uses'  => 'LogViewerController@show',
        ]);

        $this->get('download', [
            'as'    => 'download',
            'uses'  => 'LogViewerController@download',
        ]);

        $this->get('{level}', [
            'as'    => 'filter',
            'uses'  => 'LogViewerController@showByLevel',
        ]);
    }
}
