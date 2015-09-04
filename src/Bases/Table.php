<?php namespace Arcanedev\LogViewer\Bases;

use Arcanedev\LogViewer\Contracts\LogLevelsInterface;
use Arcanedev\LogViewer\Contracts\TableInterface;
use Illuminate\Translation\Translator;

/**
 * Class Table
 * @package Arcanedev\LogViewer\Bases
 */
abstract class Table implements TableInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var array  */
    protected $header  = [];

    /** @var array  */
    protected $rows    = [];

    /** @var array  */
    protected $footer  = [];

    /** @var LogLevelsInterface */
    protected $levels;

    /** @var string|null */
    protected $locale;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a table instance.
     *
     * @param  array               $data
     * @param  LogLevelsInterface  $levels
     * @param  string|null         $locale
     */
    public function __construct(array $data, LogLevelsInterface $levels, $locale = null)
    {
        $this->setLevels($levels);
        $this->setLocale($locale);

        $this->init($data);
    }

    /**
     * Prepare the table.
     *
     * @param  array  $data
     */
    private function init($data)
    {
        $this->header  = $this->prepareHeader($data);
        $this->rows    = $this->prepareRows($data);
        $this->footer  = $this->prepareFooter($data);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set LogLevels instance.
     *
     * @param  LogLevelsInterface  $levels
     *
     * @return self
     */
    protected function setLevels(LogLevelsInterface $levels)
    {
        $this->levels = $levels;

        return $this;
    }

    /**
     * Set locale.
     *
     * @param  string|null  $locale
     *
     * @return self
     */
    private function setLocale($locale)
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
     * Get translator instance.
     *
     * @return Translator
     */
    protected static function trans()
    {
        /** @var Translator $translator */
        $translator = trans();

        return $translator;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
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
        return self::trans()->get('log-viewer::' . $key,  [], $this->locale);
    }
}
