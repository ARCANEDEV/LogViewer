<?php namespace Arcanedev\LogViewer\Bases;

use Arcanedev\LogViewer\Contracts\Utilities\LogLevels as LogLevelsContract;
use Arcanedev\LogViewer\Contracts\Table as TableContract;

/**
 * Class     Table
 *
 * @package  Arcanedev\LogViewer\Bases
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class Table implements TableContract
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var array  */
    private $header  = [];

    /** @var array  */
    private $rows    = [];

    /** @var array  */
    private $footer  = [];

    /** @var \Arcanedev\LogViewer\Contracts\Utilities\LogLevels */
    protected $levels;

    /** @var string|null */
    protected $locale;

    /** @var array */
    private $data = [];

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a table instance.
     *
     * @param  array                                               $data
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\LogLevels  $levels
     * @param  string|null                                         $locale
     */
    public function __construct(array $data, LogLevelsContract $levels, $locale = null)
    {
        $this->setLevels($levels);
        $this->setLocale(is_null($locale) ? config('log-viewer.locale') : $locale);
        $this->setData($data);
        $this->init();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set LogLevels instance.
     *
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\LogLevels  $levels
     *
     * @return \Arcanedev\LogViewer\Bases\Table
     */
    protected function setLevels(LogLevelsContract $levels)
    {
        $this->levels = $levels;

        return $this;
    }

    /**
     * Set table locale.
     *
     * @param  string|null  $locale
     *
     * @return \Arcanedev\LogViewer\Bases\Table
     */
    protected function setLocale($locale)
    {
        if (is_null($locale) || $locale === 'auto') {
            $locale = app()->getLocale();
        }

        $this->locale = $locale;

        return $this;
    }

    /**
     * Get table header.
     *
     * @return array
     */
    public function header()
    {
        return $this->header;
    }

    /**
     * Get table rows.
     *
     * @return array
     */
    public function rows()
    {
        return $this->rows;
    }

    /**
     * Get table footer.
     *
     * @return array
     */
    public function footer()
    {
        return $this->footer;
    }

    /**
     * Get raw data.
     *
     * @return array
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Set table data.
     *
     * @param  array  $data
     *
     * @return self
     */
    private function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Prepare the table.
     */
    private function init()
    {
        $this->header = $this->prepareHeader($this->data);
        $this->rows   = $this->prepareRows($this->data);
        $this->footer = $this->prepareFooter($this->data);
    }

    /**
     * Prepare table header.
     *
     * @param  array  $data
     *
     * @return array
     */
    abstract protected function prepareHeader(array $data);

    /**
     * Prepare table rows.
     *
     * @param  array  $data
     *
     * @return array
     */
    abstract protected function prepareRows(array $data);

    /**
     * Prepare table footer.
     *
     * @param  array  $data
     *
     * @return array
     */
    abstract protected function prepareFooter(array $data);

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Translate.
     *
     * @param  string $key
     *
     * @return string
     */
    protected function translate($key)
    {
        /** @var \Illuminate\Translation\Translator $translator */
        $translator = trans();

        return $translator->get('log-viewer::' . $key,  [], $this->locale);
    }

    /**
     * Get log level color.
     *
     * @param  string  $level
     *
     * @return string
     */
    protected function color($level)
    {
        return log_styler()->color($level);
    }
}
