<?php namespace Arcanedev\LogViewer\Http\Routes;

use Arcanedev\Support\Routing\RouteRegistrar;

/**
 * Class     LogViewerRoute
 *
 * @package  Arcanedev\LogViewer\Http\Routes
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @codeCoverageIgnore
 */
class LogViewerRoute extends RouteRegistrar
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Map all routes.
     */
    public function map()
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
        $this->prefix('logs')->group(function() {
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
        $this->prefix('{date}')->group(function() {
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
