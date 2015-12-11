<?php namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\FilesystemInterface;
use Arcanedev\LogViewer\Contracts\LogCheckerInterface;
use Illuminate\Contracts\Config\Repository as Config;

/**
 * Class     LogChecker
 *
 * @package  Arcanedev\LogViewer\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @todo     Adding the translation or not ??
 */
class LogChecker implements LogCheckerInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Constants
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @link http://laravel.com/docs/5.1/errors#configuration
     * @link https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md#log-to-files-and-syslog
     */
    const HANDLER_DAILY    = 'daily';
    const HANDLER_SINGLE   = 'single';
    const HANDLER_SYSLOG   = 'syslog';
    const HANDLER_ERRORLOG = 'errorlog';

    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The config repository instance.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * The filesystem instance.
     *
     * @var \Arcanedev\LogViewer\Contracts\FilesystemInterface
     */
    private $filesystem;

    /**
     * Log handler mode.
     *
     * @var string
     */
    protected $handler  = '';

    /**
     * The check status.
     *
     * @var bool
     */
    private $status     = true;

    /**
     * The check messages.
     *
     * @var array
     */
    private $messages;

    /**
     * Log files status (or statuses... i don't know).
     *
     * @var array
     */
    private $files;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make LogChecker instance.
     *
     * @param  \Illuminate\Contracts\Config\Repository             $config
     * @param  \Arcanedev\LogViewer\Contracts\FilesystemInterface  $filesystem
     */
    public function __construct(Config $config, FilesystemInterface $filesystem)
    {
        $this->setConfig($config);
        $this->setFilesystem($filesystem);
        $this->files      = [];

        $this->refresh();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the config instance.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     *
     * @return self
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set the Filesystem instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\FilesystemInterface  $filesystem
     *
     * @return self
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Set the log handler mode.
     *
     * @param  string  $handler
     *
     * @return self
     */
    protected function setHandler($handler)
    {
        $this->handler = strtolower($handler);

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get messages.
     *
     * @return array
     */
    public function messages()
    {
        $this->refresh();

        return $this->messages;
    }

    /**
     * Check if the checker passes.
     *
     * @return bool
     */
    public function passes()
    {
        $this->refresh();

        return $this->status;
    }

    /**
     * Check if the checker fails.
     *
     * @return bool
     */
    public function fails()
    {
        return ! $this->passes();
    }

    /**
     * Get the requirements.
     *
     * @return array
     */
    public function requirements()
    {
        $this->refresh();

        if ($this->isDaily()) {
            return [
                'status'    => 'success',
                'header'    => 'Application requirements fulfilled.',
                'message'   => 'Are you ready to rock ?',
            ];
        }

        return [
            'status'    => 'failed',
            'header'    => 'Application requirements failed.',
            'message'   => $this->messages['handler']
        ];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Check Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Is a daily handler mode ?
     *
     * @return bool
     */
    protected function isDaily()
    {
        return $this->isSameHandler(self::HANDLER_DAILY);
    }

    /**
     * Is the handler is the same as the application log handler.
     *
     * @param  string  $handler
     *
     * @return bool
     */
    private function isSameHandler($handler)
    {
        return $this->handler === $handler;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Refresh the checks.
     *
     * @return self
     */
    private function refresh()
    {
        $this->setHandler($this->config->get('app.log', 'single'));

        $this->messages   = [
            'handler'   => '',
            'files'     => [],
        ];
        $this->files      = [];

        $this->checkHandler();
        $this->checkLogFiles();

        return $this;
    }

    /**
     * Check the handler mode
     */
    private function checkHandler()
    {
        if ($this->isDaily()) {
            return;
        }

        $this->messages['handler'] = implode(' ', [
            'You should set the log handler to `daily` mode.',
            'Please check the LogViwer wiki page (Requirements) for more details.'
        ]);
    }

    /**
     * Check all log files.
     *
     * @return array
     */
    private function checkLogFiles()
    {
        foreach ($this->filesystem->all() as $path) {
            $this->checkLogFile($path);
        }
    }

    /**
     * Check a log file.
     *
     * @param  string  $path
     */
    private function checkLogFile($path)
    {
        $status  = true;
        $file    = basename($path);
        $message = "The log file [$file] is valid.";

        if ($this->isSingleLogFile($file)) {
            $this->status                   = $status  = false;
            $this->messages['files'][$file] = $message =
                "You have a single log file in your application, you should split the [$file] into seperate log files.";
        }
        elseif ($this->isInvalidLogDate($file)) {
            $this->status                   = $status  = false;
            $this->messages['files'][$file] = $message =
                "The log file [$file] has an invalid date, the format must be like laravel-YYYY-MM-DD.log.";
        }


        $this->files[$file] = compact('filename', 'status', 'message', 'path');
    }

    /**
     * Check if it's not a single log file.
     *
     * @param  string  $file
     *
     * @return bool
     */
    private function isSingleLogFile($file)
    {
        return $file === 'laravel.log';
    }

    /**
     * Check the date of the log file.
     *
     * @param  string  $file
     *
     * @return bool
     */
    private function isInvalidLogDate($file)
    {
        $pattern = '/laravel-(\d){4}-(\d){2}-(\d){2}.log/';

        if ((bool) preg_match($pattern, $file, $matches) === false) {
            return true;
        }

        return false;
    }
}
