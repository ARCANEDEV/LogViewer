<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Reader;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Arcanedev\LogViewer\Index\IndexManager;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class     LogPager
 *
 * Per-file bundle handed to a streaming {@see \Arcanedev\LogViewer\Entities\Log}.
 * Exposes exactly what the viewer needs — paginated pages, search, and level
 * counts (from the index header, so the menu/stats cost nothing to render).
 */
class LogPager
{
    /** @var string */
    protected $logPath;

    /** @var \Arcanedev\LogViewer\Contracts\FormatDriver */
    protected $driver;

    /** @var \Arcanedev\LogViewer\Reader\PageReader */
    protected $pages;

    /** @var \Arcanedev\LogViewer\Reader\SearchReader */
    protected $search;

    /** @var \Arcanedev\LogViewer\Index\IndexManager */
    protected $manager;

    public function __construct(
        string $logPath,
        FormatDriver $driver,
        PageReader $pages,
        SearchReader $search,
        IndexManager $manager
    ) {
        $this->logPath = $logPath;
        $this->driver  = $driver;
        $this->pages   = $pages;
        $this->search  = $search;
        $this->manager = $manager;
    }

    /**
     * Paginate entries, newest-first.
     */
    public function paginate(string $level, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->pages->page($this->logPath, $this->driver, $level, $page, $perPage);
    }

    /**
     * Search entries, newest-first.
     *
     * @param  array<int,string>  $needles
     */
    public function searchEntries(array $needles, string $level, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->search->search($this->logPath, $this->driver, $needles, $level, $perPage, $page);
    }

    /**
     * Lazily iterate all entries (file order), O(1) memory.
     *
     * @return \Generator<\Arcanedev\LogViewer\Contracts\LogEntryInterface>
     */
    public function streamEntries(string $level = 'all'): \Generator
    {
        yield from $this->pages->stream($this->logPath, $this->driver, $level);
    }

    /**
     * Level counts (by name) from the index header, plus the `all` total.
     *
     * @return array<string,int>
     */
    public function levelCounts(): array
    {
        $header = $this->manager->ensureReady($this->logPath, $this->driver);

        // Index still warming: report zeros for now (menu fills in once ready).
        if ($header === null) {
            return ['all' => 0];
        }

        return ['all' => $header->entryCount] + $header->levelCountsByName();
    }
}
