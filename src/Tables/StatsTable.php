<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tables;

use Arcanedev\LogViewer\Contracts\Utilities\LogLevels as LogLevelsContract;
use Illuminate\Support\{Arr, Collection};

/**
 * Class     StatsTable
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class StatsTable extends AbstractTable
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Make a stats table instance.
     *
     * @param  array                                               $data
     * @param  \Arcanedev\LogViewer\Contracts\Utilities\LogLevels  $levels
     * @param  string|null                                         $locale
     *
     * @return $this
     */
    public static function make(array $data, LogLevelsContract $levels, $locale = null)
    {
        return new static($data, $levels, $locale);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
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
                'date' => __('Date'),
                'all'  => __('All'),
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
     * Get totals.
     *
     * @param  string|null  $locale
     *
     * @return \Illuminate\Support\Collection
     */
    public function totals($locale = null)
    {
        $totals = Collection::make();

        foreach (Arr::except($this->footer(), 'all') as $level => $count) {
            $totals->put($level, [
                'label'     => log_levels()->get($level, $locale),
                'value'     => $count,
                'color'     => $this->color($level),
                'highlight' => $this->color($level),
            ]);
        }

        return $totals;
    }

    /**
     * Get json totals data.
     *
     * @param  string|null  $locale
     *
     * @return string
     */
    public function totalsJson($locale = null)
    {
        return $this->totals($locale)->toJson(JSON_PRETTY_PRINT);
    }
}
