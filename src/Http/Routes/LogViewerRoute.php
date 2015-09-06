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

        $this->registerDashboardRoute();

        $this->registerLogsRoutes();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Route Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register dashboard route
     */
    private function registerDashboardRoute()
    {
        $this->get('/', [
            'as'    => 'dashboard',
            'uses'  => 'LogViewerController@index',
        ]);
    }

    /**
     * Register logs routes
     */
    private function registerLogsRoutes()
    {
        $this->group([
            'as'     => 'logs.',
            'prefix' => 'logs/{date}',
        ], function() {
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
        });
    }
}
