<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\Utilities\LogStyler as LogStylerContract;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Support\HtmlString;

/**
 * Class     LogStyler
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogStyler implements LogStylerContract
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The config repository instance.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Create a new instance.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     */
    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get config.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    private function get($key, $default = null)
    {
        return $this->config->get("log-viewer.$key", $default);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Make level icon.
     *
     * @param  string       $level
     * @param  string|null  $default
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function icon($level, $default = null)
    {
        return new HtmlString(
            '<i class="'.$this->get("icons.$level", $default).'"></i>'
        );
    }

    /**
     * Get level color.
     *
     * @param  string       $level
     * @param  string|null  $default
     *
     * @return string
     */
    public function color($level, $default = null)
    {
        return $this->get("colors.levels.$level", $default);
    }

    /**
     * Get strings to highlight.
     *
     * @param  array  $default
     *
     * @return array
     */
    public function toHighlight(array $default = [])
    {
        return $this->get('highlight', $default);
    }
}
