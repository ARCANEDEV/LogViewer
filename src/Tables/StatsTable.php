<?php namespace Arcanedev\LogViewer\Tables;

use Arcanedev\LogViewer\Bases\Table;
use Arcanedev\LogViewer\Contracts\LogLevelsInterface;

/**
 * Class     StatsTable
 *
 * @package  Arcanedev\LogViewer\Tables
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class StatsTable extends Table
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make a stats table instance.
     *
     * @param  array               $data
     * @param  LogLevelsInterface  $levels
     * @param  string|null         $locale
     *
     * @return self
     */
    public static function make(array $data, LogLevelsInterface $levels, $locale = null)
    {
        return new self($data, $levels, $locale);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Prepare table header.
     *
     * @param  array  $data
     *
     * @return array
     */
    protected function prepareHeader(array $data)
    {
        return array_merge_recursive(
            [
                'date' => $this->translate('general.date'),
                'all'  => $this->translate('general.all'),
            ],
            $this->levels->names($this->locale)
        );
    }

    /**
     * Prepare table rows.
     *
     * @param  array  $data
     *
     * @return array
     */
    protected function prepareRows(array $data)
    {
        $rows = [];

        foreach ($data as $date => $levels) {
            $rows[$date] = array_merge(compact('date'), $levels);
        }

        return $rows;
    }

    /**
     * Prepare table footer.
     *
     * @param  array  $data
     *
     * @return array
     */
    protected function prepareFooter(array $data)
    {
        $footer = [];

        foreach ($data as $date => $levels) {
            foreach ($levels as $level => $count) {
                if ( ! isset($footer[$level])) {
                    $footer[$level] = 0;
                }

                $footer[$level] += $count;
            }
        }

        return $footer;
    }

    /**
     * Get json chart data.
     *
     * @param  string|null $locale
     *
     * @return array
     */
    public function totalsJson($locale = null)
    {
        $this->setLocale($locale);

        $json   = [];
        $levels = array_except($this->footer(), 'all');

        foreach ($levels as $level => $count) {
            $json[] = [
                'label'     => $this->translate("levels.$level"),
                'value'     => $count,
                'color'     => $this->color($level),
                'highlight' => $this->color($level),
            ];
        }

        return json_encode(array_values($json), JSON_PRETTY_PRINT);
    }
}
