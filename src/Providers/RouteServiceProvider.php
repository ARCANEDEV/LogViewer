<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\Support\Laravel\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class     RouteServiceProvider
 *
 * @package  Arcanedev\LogViewer\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the routes namespace
     *
     * @return string
     */
    protected function getRouteNamespace()
    {
        return 'Arcanedev\\LogViewer\\Http\\Routes';
    }

    /**
     * Define the routes for the application.
     *
     * @param  Router  $router
     */
    public function map(Router $router)
    {
        $this->mapRoutes($router, __DIR__, []);
    }
}
