<?php namespace Arcanedev\LogViewer\Contracts\Utilities;

use Illuminate\Contracts\Config\Repository as ConfigContract;

/**
 * Interface  LogChecker
 *
 * @package   Arcanedev\LogViewer\Contracts\Utilities
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface LogChecker
{
    /* -----------------------------------------------------------------
     |  Constants
     | -----------------------------------------------------------------
     */

    /**
     * @link http://laravel.com/docs/5.4/errors#configuration
     * @link https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md#log-to-files-and-syslog
     */
    const HANDLER_DAILY    = 'daily';
    const HANDLER_SINGLE   = 'single';
    const HANDLER_SYSLOG   = 'syslog';
    const HANDLER_ERRORLOG = 'errorlog';

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Set the config instance.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     *
     * @return self
     */
    public function setConfig(ConfigContract $config);

    /**
     * Set the Filesystem instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\Filesystem  $filesystem
     *
     * @return self
     */
    public function setFilesystem(Filesystem $filesystem);

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get messages.
     *
     * @return array
     */
    public function messages();

    /**
     * Check passes ??
     *
     * @return bool
     */
    public function passes();

    /**
     * Check fails ??
     *
     * @return bool
     */
    public function fails();

    /**
     * Get the requirements
     *
     * @return array
     */
    public function requirements();
}
