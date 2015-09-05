<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\Support\Laravel\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class RouteServiceProvider
 * @package Arcanedev\LogViewer\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    protected $namespace = 'Arcanedev\\LogViewer\\Http\\Routes';

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Define the routes for the application.
     *
     * @param  Router  $router
     */
    public function map(Router $router)
    {
        $this->mapRoutes(__DIR__, []);
    }
}
