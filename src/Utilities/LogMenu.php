<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\LogMenuInterface;
use Arcanedev\LogViewer\Contracts\LogStylerInterface;
use Arcanedev\LogViewer\Entities\Log;
use Illuminate\Config\Repository as Config;

/**
 * Class     LogMenu
 *
 * @package  Arcanedev\LogViewer\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogMenu implements LogMenuInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The config repository.
     *
     * @var Config
     */
    protected $config;

    /**
     * The log styler instance.
     *
     * @var LogStylerInterface
     */
    private $styler;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create the LogMenu instance.
     *
     * @param  Config              $config
     * @param  LogStylerInterface  $styler
     */
    public function __construct(Config $config, LogStylerInterface $styler)
    {
        $this->config = $config;
        $this->styler = $styler;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make log menu.
     *
     * @param  Log   $log
     * @param  bool  $trans
     *
     * @return array
     */
    public function make(Log $log, $trans = true)
    {
        $items = [];

        foreach($log->tree($trans) as $level => $item) {
            $items[$level] = array_merge($item, [
                'url'  => route('log-viewer::logs.filter', [$log->date, $level]),
                'icon' => $this->isIconsEnabled() ? $this->styler->icon($level) : '',
            ]);
        }

        return $items;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Check Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Check if the icons are enabled.
     *
     * @return bool
     */
    private function isIconsEnabled()
    {
        return (bool) $this->config('menu.icons-enabled', false);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get config.
     *
     * @param  string      $key
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    private function config($key, $default = null)
    {
        return $this->config->get('log-viewer.' . $key, $default);
    }
}
