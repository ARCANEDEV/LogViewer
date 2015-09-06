<?php namespace Arcanedev\LogViewer\Http\Controllers;

use Arcanedev\LogViewer\Bases\Controller;

/**
 * Class     LogViewerController
 *
 * @package  LogViewer\Http\Controllers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @todo     Refactoring & Testing
 */
class LogViewerController extends Controller
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Show the dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $stats     = $this->logViewer->statsTable();

        $headers   = $stats->header();
        $rows      = $stats->rows();
        $footer    = $stats->footer();
        $reports   = $stats->totalsJson();
        $percents  = $this->calcPercentages($footer, $headers);

        return view(
            'log-viewer::dashboard',
            compact('headers', 'rows', 'footer', 'reports', 'percents')
        );
    }

    /**
     * Show the log
     *
     * @param  string  $date
     *
     * @return \Illuminate\View\View
     */
    public function show($date)
    {
        $log     = $this->logViewer->get($date);
        $levels  = $this->logViewer->levelsNames();
        $entries = $log->entries();

        return view('log-viewer::show', compact('log', 'levels', 'entries'));
    }

    /**
     * Filter the log entries by level
     *
     * @param  string $date
     * @param  string $level
     *
     * @return \Illuminate\View\View
     */
    public function showByLevel($date, $level)
    {
        $log     = $this->logViewer->get($date);
        $levels  = $this->logViewer->levelsNames();
        $entries = $this->logViewer->entries($date, $level);

        return view('log-viewer::show', compact('log', 'levels', 'entries'));
    }

    /**
     * Download the log
     *
     * @param  string  $date
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($date)
    {
        return $this->logViewer->download($date);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    private function calcPercentages(array $total, array $names)
    {
        $percents = [];
        $all      = array_get($total, 'all');

        foreach ($total as $level => $count) {
            $percents[$level] = [
                'name'    => $names[$level],
                'count'   => $count,
                'percent' => round(($count / $all) * 100, 2),
            ];
        }

        return $percents;
    }
}
