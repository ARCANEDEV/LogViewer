<?php namespace Arcanedev\LogViewer\Http\Routes;

use Arcanedev\Support\Bases\RouteRegister;
use Illuminate\Contracts\Routing\Registrar as Router;

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
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     */
    public function map(Router $router)
    {
        $this->get('/', [
            'as'    => 'log-viewer::dashboard',
            'uses'  => 'LogViewerController@index',
        ]);

        $this->registerLogsRoutes();
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
        $this->group([
            'prefix' => 'logs',
        ], function() {
            $this->get('/', [
                'as'    => 'log-viewer::logs.list',
                'uses'  => 'LogViewerController@listLogs',
            ]);

            $this->delete('delete', [
                'as'    => 'log-viewer::logs.delete',
                'uses'  => 'LogViewerController@delete',
            ]);

            $this->registerSingleLogRoutes();
        });
    }

    /**
     * Register single log routes.
     */
    private function registerSingleLogRoutes()
    {
        $this->group([
            'prefix'    => '{date}',
        ], function() {
            $this->get('/', [
                'as'    => 'log-viewer::logs.show',
                'uses'  => 'LogViewerController@show',
            ]);

            $this->get('download', [
                'as'    => 'log-viewer::logs.download',
                'uses'  => 'LogViewerController@download',
            ]);

            $this->get('{level}', [
                'as'    => 'log-viewer::logs.filter',
                'uses'  => 'LogViewerController@showByLevel',
            ]);
        });
    }
}
