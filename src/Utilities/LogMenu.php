<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\LogMenuInterface;
use Illuminate\Config\Repository as Config;
use Illuminate\Translation\Translator;

/**
 * Class LogMenu
 * @package Arcanedev\LogViewer\Utilities
 */
class LogMenu implements LogMenuInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Translator
     */
    private $trans;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    public function __construct(Config $config, $translator)
    {
        $this->config = $config;
        $this->trans  = $translator;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get level name
     *
     * @param  string  $level
     * @param  bool    $trans
     *
     * @return string
     */
    private function getName($level, $trans)
    {
        if ($trans) {
            $level = $this->trans('levels.' . $level);
        }

        return $level;
    }

    /**
     * Get icon
     *
     * @param  string  $level
     *
     * @return string|null
     */
    private function getIcon($level)
    {
        return $this->config('icons.' . $level);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make menu item
     *
     * @param  string      $level
     * @param  int         $count
     * @param  bool|false  $translateName
     * @param  bool|false  $withIcon
     *
     * @return array
     */
    public function item($level, $count, $translateName = false, $withIcon = false)
    {
        $item = [];

        $item['name']  = $this->getName($level, $translateName);

        if ($this->isIconsEnabled() && $withIcon) {
            $item['icon'] = $this->getIcon($level);
        }

        $item['count'] = $count;

        return $item;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Check Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Is icons enabled from configs ?
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
     * Get config
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

    /**
     * Translate
     *
     * @param  string  $key
     *
     * @return string
     */
    private function trans($key)
    {
        return $this->trans->get('log-viewer::' . $key);
    }
}
