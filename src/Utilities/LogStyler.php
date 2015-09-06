<?php namespace Arcanedev\LogViewer\Utilities;

use Illuminate\Contracts\Config\Repository as Config;

/**
 * Class     LogStyler
 *
 * @package  Arcanedev\LogViewer\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogStyler
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var Config */
    protected $config;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new instance.
     *
     * @param  Config  $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get config
     *
     * @param  string      $key
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return $this->config->get('log-viewer.' . $key, $default);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make level icon.
     *
     * @param  string  $level
     *
     * @return string
     */
    public function icon($level)
    {
        return '<i class="' . $this->config('icons.' . $level) . '"></i>';
    }

    /**
     * Get level color.
     *
     * @param  string  $level
     *
     * @return string
     */
    public function color($level)
    {
        return $this->config('colors.levels.' . $level . '.background');
    }
}
