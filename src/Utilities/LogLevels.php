<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\LogLevelsInterface;
use Illuminate\Translation\Translator;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Class     LogLevels
 *
 * @package  Arcanedev\LogViewer\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
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
     * The Translator instance.
     *
     * @var \Illuminate\Translation\Translator
     */
    private $translator;

    /**
     * The selected locale.
     *
     * @var string
     */
    private $locale;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create LogLevels instance.
     *
     * @param  \Illuminate\Translation\Translator  $translator
     * @param  string                              $locale
     */
    public function __construct(Translator $translator, $locale)
    {
        $this->setTranslator($translator);
        $this->setLocale($locale);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the Translator instance.
     *
     * @param  \Illuminate\Translation\Translator  $translator
     *
     * @return self
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * Get the selected locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale === 'auto'
            ? $this->translator->getLocale()
            : $this->locale;
    }

    /**
     * Set the selected locale.
     *
     * @param  string  $locale
     *
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = is_null($locale) ? 'auto' : $locale;

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the log levels.
     *
     * @param  bool|false  $flip
     *
     * @return array
     */
    public function lists($flip = false)
    {
        return self::all($flip);
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
        $levels = self::all(true);

        array_walk($levels, function (&$name, $level) use ($locale) {
            $name = $this->get($level, $locale);
        });

        return $levels;
    }

    /**
     * Get PSR log levels.
     *
     * @param  bool|false  $flip
     *
     * @return array
     */
    public static function all($flip = false)
    {
        if (empty(self::$levels)) {
            $class        = new ReflectionClass(new LogLevel);
            self::$levels = $class->getConstants();
        }

        return $flip ? array_flip(self::$levels) : self::$levels;
    }

    /**
     * Get the translated level.
     *
     * @param  string       $key
     * @param  string|null  $locale
     *
     * @return string
     */
    public function get($key, $locale = null)
    {
        if (is_null($locale) || $locale === 'auto') {
            $locale = $this->getLocale();
        }

        return $this->translator->get("log-viewer::levels.$key", [], $locale);
    }
}
