<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\LogLevelsInterface;
use Psr\Log\LogLevel;
use ReflectionClass;
use Illuminate\Translation\Translator;

/**
 * Class LogLevels
 * @package Arcanedev\LogViewer\Log
 */
class LogLevels implements LogLevelsInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The log levels.
     *
     * @var array
     */
    protected static $levels = [];

    /**
     * @var Translator
     */
    private $translator;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log levels.
     *
     * @return array
     */
    public function lists()
    {
        return self::all();
    }

    /**
     * Get translated levels.
     *
     * @param  string|null  $locale
     *
     * @return array
     */
    public function names($locale = null)
    {
        if ($locale == 'auto') {
            $locale = null;
        }

        $levels = array_values(self::all());

        return array_map(function($level) use ($locale) {
            return $this->getTranslatedName($level, $locale);
        }, array_combine($levels, $levels));
    }

    /**
     * Get PSR log levels.
     *
     * @return array
     */
    public static function all()
    {
        if (empty(self::$levels)) {
            $class        = new ReflectionClass(new LogLevel);
            self::$levels = $class->getConstants();
        }

        return self::$levels;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Translate a level
     *
     * @param  string       $level
     * @param  string|null  $locale
     *
     * @return string
     */
    private function getTranslatedName($level, $locale)
    {
        return $this->translator->get('log-viewer::levels.' . $level, [], $locale);
    }
}
