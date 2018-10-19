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
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Map all routes.
     */
    public function map()
    {
        $this->name('log-viewer::')->group(function () {
            // log-viewer::dashboard
            $this->get('/', 'LogViewerController@index')->name('dashboard');

            $this->mapLogsRoutes();
        });
    }

    /**
     * Map the logs routes.
     */
    private function mapLogsRoutes()
    {
        $this->prefix('logs')->name('logs.')->group(function() {
            $this->get('/', 'LogViewerController@listLogs')
                 ->name('list'); // log-viewer::logs.list

            $this->delete('delete', 'LogViewerController@delete')
                 ->name('delete'); // log-viewer::logs.delete

            $this->prefix('{date}')->group(function() {
                $this->get('/', 'LogViewerController@show')
                     ->name('show'); // log-viewer::logs.show

                $this->get('download', 'LogViewerController@download')
                     ->name('download'); // log-viewer::logs.download

                $this->get('{level}', 'LogViewerController@showByLevel')
                     ->name('filter'); // log-viewer::logs.filter

                $this->get('{level}/search', 'LogViewerController@search')
                     ->name('search'); // log-viewer::logs.search
            });
        });
    }
}
