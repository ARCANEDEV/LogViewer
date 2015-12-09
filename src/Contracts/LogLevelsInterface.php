<?php namespace Arcanedev\LogViewer\Contracts;

use Illuminate\Translation\Translator;

/**
 * Interface  LogLevelsInterface
 *
 * @package   Arcanedev\LogViewer\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface LogLevelsInterface
{
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
    public function setTranslator(Translator $translator);

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
    public function lists($flip = false);

    /**
     * Get translated levels.
     *
     * @param  string|null  $locale
     *
     * @return array
     */
    public function names($locale = null);

    /**
     * Get PSR log levels.
     *
     * @param  bool|false  $flip
     *
     * @return array
     */
    public static function all($flip = false);
}
