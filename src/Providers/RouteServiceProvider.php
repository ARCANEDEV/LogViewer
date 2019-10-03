<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\LogViewer\Http\Routes\LogViewerRoute;
use Arcanedev\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Class     RouteServiceProvider
 *
 * @package  Arcanedev\LogViewer\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{
    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get Route attributes
     *
     * @return array
     */
    public function routeAttributes()
    {
        return array_merge($this->config('attributes', []), [
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

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        if ($this->isEnabled()) {
            $this->group($this->routeAttributes(), function() {
                LogViewerRoute::register();
            });
        }
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
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
        return $this->app['config']->get("log-viewer.route.$key", $default);
    }
}
