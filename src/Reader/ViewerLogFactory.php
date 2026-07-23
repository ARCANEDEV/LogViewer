<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Reader;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Format\DriverRegistry;
use Arcanedev\LogViewer\Index\IndexManager;

/**
 * Class     ViewerLogFactory
 *
 * Builds a {@see Log} for the viewer, choosing the streaming engine for large
 * files and the classic whole-file read for small ones. Shared by both the
 * single-log path ({@see \Arcanedev\LogViewer\Utilities\Factory::logForViewer()})
 * and the cross-file aggregation ({@see \Arcanedev\LogViewer\Entities\LogCollection}),
 * so the dashboard / logs-list stats are streamed too.
 */
class ViewerLogFactory
{
    /** @var \Arcanedev\LogViewer\Format\DriverRegistry */
    protected $registry;

    /** @var \Arcanedev\LogViewer\Reader\PageReader */
    protected $pages;

    /** @var \Arcanedev\LogViewer\Reader\SearchReader */
    protected $search;

    /** @var \Arcanedev\LogViewer\Index\IndexManager */
    protected $manager;

    public function __construct(
        DriverRegistry $registry,
        PageReader $pages,
        SearchReader $search,
        IndexManager $manager
    ) {
        $this->registry = $registry;
        $this->pages    = $pages;
        $this->search   = $search;
        $this->manager  = $manager;
    }

    /**
     * Build a viewer log for a date + path.
     *
     * @param  callable():string  $rawLoader  Loads the raw content for the classic path.
     *
     * @return \Arcanedev\LogViewer\Entities\Log
     */
    public function make(string $date, string $path, callable $rawLoader): Log
    {
        return $this->applies($path)
            ? Log::streaming($date, $path, $this->pagerFor($path))
            : new Log($date, $path, $rawLoader());
    }

    /**
     * Whether the streaming engine should serve this file.
     */
    public function applies(string $path): bool
    {
        if ( ! config('log-viewer.streaming.enabled', true)) {
            return false;
        }

        if ( ! $this->registry->driver()->supportsStreaming()) {
            return false;
        }

        $size     = @filesize($path);
        $maxInline = (int) config('log-viewer.streaming.max-inline-bytes', 2 * 1024 * 1024);

        return $size !== false && $size > $maxInline;
    }

    /**
     * Build the per-file pager.
     */
    public function pagerFor(string $path): LogPager
    {
        return new LogPager($path, $this->registry->driver(), $this->pages, $this->search, $this->manager);
    }
}
