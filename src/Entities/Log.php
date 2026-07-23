<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Entities;

use Illuminate\Contracts\Support\{Arrayable, Jsonable};
use Illuminate\Support\Carbon;
use JsonSerializable;
use SplFileInfo;

/**
 * Class     Log
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class Log implements Arrayable, Jsonable, JsonSerializable
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var string */
    public $date;

    /** @var string */
    private $path;

    /** @var \Arcanedev\LogViewer\Entities\LogEntryCollection */
    private $entries;

    /** @var \SplFileInfo */
    private $file;

    /** @var \Arcanedev\LogViewer\Reader\LogPager|null Streaming backend (null = classic). */
    private $pager;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Log constructor.
     *
     * @param  string  $date
     * @param  string  $path
     * @param  string  $raw
     */
    public function __construct($date, $path, $raw = null)
    {
        $this->date = $date;
        $this->path = $path;
        $this->file = new SplFileInfo($path);

        // Classic path: eager (but lazily-yielded) entry collection.
        // When $raw is null the log is served through a streaming pager instead.
        if ($raw !== null) {
            $this->entries = LogEntryCollection::load($raw);
        }
    }

    /**
     * Make a streaming-backed log (no whole-file read).
     *
     * @return self
     */
    public static function streaming($date, $path, \Arcanedev\LogViewer\Reader\LogPager $pager)
    {
        $log = new self($date, $path, null);
        $log->pager = $pager;

        return $log;
    }

    /**
     * Whether this log is served by the streaming engine.
     *
     * @return bool
     */
    public function isStreaming()
    {
        return $this->pager !== null;
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get log path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get file info.
     *
     * @return \SplFileInfo
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Get file size.
     *
     * @return string
     */
    public function size()
    {
        return $this->formatSize($this->file->getSize());
    }

    /**
     * Get file creation date.
     *
     * @return \Carbon\Carbon
     */
    public function createdAt()
    {
        return Carbon::createFromTimestamp($this->file()->getATime());
    }

    /**
     * Get file modification date.
     *
     * @return \Carbon\Carbon
     */
    public function updatedAt()
    {
        return Carbon::createFromTimestamp($this->file()->getMTime());
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Make a log object.
     *
     * @param  string  $date
     * @param  string  $path
     * @param  string  $raw
     *
     * @return self
     */
    public static function make($date, $path, $raw)
    {
        return new self($date, $path, $raw);
    }

    /**
     * Get log entries.
     *
     * @param  string  $level
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function entries($level = 'all')
    {
        if ($this->isStreaming()) {
            // Lazily stream entries from the index — O(1) memory. Prefer
            // paginate()/search() for the viewer; this keeps the collection API
            // (count, iteration, filters) working memory-safely on large files.
            $pager = $this->pager;

            return new LogEntryCollection(function () use ($pager, $level) {
                yield from $pager->streamEntries($level);
            });
        }

        return $level === 'all'
            ? $this->entries
            : $this->getByLevel($level);
    }

    /**
     * Paginate entries newest-first (streaming-aware).
     *
     * @param  string  $level
     * @param  int     $perPage
     * @param  int     $page
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($level = 'all', $perPage = 30, $page = 1)
    {
        return $this->isStreaming()
            ? $this->pager->paginate($level, $perPage, $page)
            : $this->entries($level)->sortDesc()->paginate($perPage);
    }

    /**
     * Search entries newest-first (streaming-aware).
     *
     * @param  array<int,string>  $needles
     * @param  string             $level
     * @param  int                $perPage
     * @param  int                $page
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function search(array $needles, $level = 'all', $perPage = 30, $page = 1)
    {
        if ($this->isStreaming()) {
            return $this->pager->searchEntries($needles, $level, $perPage, $page);
        }

        // Classic in-memory search (unchanged semantics).
        return $this->entries($level)
            ->unless(empty($needles), function (LogEntryCollection $entries) use ($needles) {
                return $entries->filter(function ($entry) use ($needles) {
                    foreach ([$entry->header, $entry->stack, $entry->context()] as $subject) {
                        if (\Illuminate\Support\Str::containsAll(\Illuminate\Support\Str::lower((string) $subject), $needles)) {
                            return true;
                        }
                    }

                    return false;
                });
            })
            ->sortDesc()
            ->paginate($perPage);
    }

    /**
     * Get filtered log entries by level.
     *
     * @param  string  $level
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    public function getByLevel($level)
    {
        return $this->entries->filterByLevel($level);
    }

    /**
     * Get log stats.
     *
     * @return array
     */
    public function stats()
    {
        return $this->isStreaming()
            ? $this->streamingStats()
            : $this->entries->stats();
    }

    /**
     * Build the stats counters from the index header (streaming path).
     *
     * Produces the same shape as LogEntryCollection::stats(): a zeroed map of
     * `all` + every level, filled from the header's level counts.
     *
     * @return array
     */
    private function streamingStats()
    {
        $counters = array_map(function () {
            return 0;
        }, array_flip(array_merge(['all'], array_keys(log_viewer()->levels(true)))));

        foreach ($this->pager->levelCounts() as $level => $count) {
            if (array_key_exists($level, $counters)) {
                $counters[$level] = $count;
            }
        }

        return $counters;
    }

    /**
     * Get the log navigation tree.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function tree($trans = false)
    {
        if (! $this->isStreaming()) {
            return $this->entries->tree($trans);
        }

        // Mirror LogEntryCollection::tree() using header-derived stats.
        $tree = $this->streamingStats();

        array_walk($tree, function (&$count, $level) use ($trans) {
            $count = [
                'name'  => $trans ? log_levels()->get($level) : $level,
                'count' => $count,
            ];
        });

        return $tree;
    }

    /**
     * Get log entries menu.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function menu($trans = true)
    {
        return log_menu()->make($this, $trans);
    }

    /* -----------------------------------------------------------------
     |  Convert Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the log as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'date'    => $this->date,
            'path'    => $this->path,
            'entries' => $this->entries->toArray()
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Serialize the log object to json data.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Format the file size.
     *
     * @param  int  $bytes
     * @param  int  $precision
     *
     * @return string
     */
    private function formatSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision).' '.$units[$pow];
    }
}
