<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class     RouteServiceProvider
 *
 * @package  Arcanedev\LogViewer\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @codeCoverageIgnore
 */
class RouteServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get Route attributes
     *
     * @return array
     */
    public function routeAttributes()
    {
        $attributes = $this->config('attributes', []);

        return array_merge($attributes, [
            'namespace' => 'Arcanedev\\LogViewer\\Http\\Controllers',
        ]);
    }

    /**
     * Check if routes is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config('enabled', false);
    }

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
        if ($this->isEnabled()) {
            $this->mapRoutes($router, __DIR__, $this->routeAttributes());
        }
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get config value by key
     *
     * @param  string      $key
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    private function config($key, $default = null)
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app['config'];

        return $config->get('log-viewer.route.' . $key, $default);
    }
}
