<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Contracts\LogEntryInterface;
use Arcanedev\LogViewer\Helpers\LogParser;
use Carbon\Carbon;
use Illuminate\Contracts\Support\{Arrayable, Jsonable};
use JsonSerializable;

/**
 * Class     LogEntry
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntry implements Arrayable, Jsonable, JsonSerializable,LogEntryInterface
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var string */
    public $env;

    /** @var string */
    public $level;

    /** @var \Carbon\Carbon */
    public $datetime;

    /** @var string */
    public $header;

    /** @var string */
    public $stack;

    /** @var array */
    public $context = [];

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Construct the log entry instance.
     *
     * @param  string       $level
     * @param  string       $header
     * @param  string|null  $stack
     */
    public function __construct($level, $header, $stack = null)
    {
        $this->setLevel($level);
        $this->setHeader($header);
        $this->setStack($stack);
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Set the entry level.
     *
     * @param  string  $level
     *
     * @return self
     */
    private function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Set the entry header.
     *
     * @param  string  $header
     *
     * @return self
     */
    private function setHeader($header)
    {
        $this->setDatetime($this->extractDatetime($header));

        $header = $this->cleanHeader($header);

        $this->header = trim($header);

        return $this;
    }

    /**
     * Set the context.
     *
     * @param  array  $context
     *
     * @return $this
     */
    private function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Set entry environment.
     *
     * @param  string  $env
     *
     * @return self
     */
    private function setEnv($env)
    {
        $this->env = head(explode('.', $env));

        return $this;
    }

    /**
     * Set the entry date time.
     *
     * @param  string  $datetime
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntry
     */
    private function setDatetime($datetime)
    {
        $this->datetime = Carbon::createFromFormat('Y-m-d H:i:s', $datetime);

        return $this;
    }

    /**
     * Set the entry stack.
     *
     * @param  string  $stack
     *
     * @return self
     */
    private function setStack($stack)
    {
        $this->stack = $stack;

        return $this;
    }

    /**
     * Get translated level name with icon.
     *
     * @return string
     */
    public function level()
    {
        return $this->icon()->toHtml().' '.$this->name();
    }

    /**
     * Get translated level name.
     *
     * @return string
     */
    public function name()
    {
        return log_levels()->get($this->level);
    }

    /**
     * Get level icon.
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function icon()
    {
        return log_styler()->icon($this->level);
    }

    /**
     * Get the entry stack.
     *
     * @return string
     */
    public function stack()
    {
        return trim(htmlentities($this->stack));
    }

    /**
     * Get the entry context as json pretty print.
     */
    public function context(int $options = JSON_PRETTY_PRINT): string
    {
        return json_encode($this->context, $options);
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if same log level.
     *
     * @param  string  $level
     *
     * @return bool
     */
    public function isSameLevel($level)
    {
        return $this->level === $level;
    }

    /* -----------------------------------------------------------------
     |  Convert Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the log entry as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'level'    => $this->level,
            'datetime' => $this->datetime->format('Y-m-d H:i:s'),
            'header'   => $this->header,
            'stack'    => $this->stack
        ];
    }

    /**
     * Convert the log entry to its JSON representation.
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
     * Serialize the log entry object to json data.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if the entry has a stack.
     *
     * @return bool
     */
    public function hasStack()
    {
        return $this->stack !== "\n";
    }

    /**
     * Check if the entry has a context.
     *
     * @return bool
     */
    public function hasContext()
    {
        return ! empty($this->context);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Clean the entry header.
     *
     * @param  string  $header
     *
     * @return string
     */
    private function cleanHeader($header)
    {
        // REMOVE THE DATE
        $header = preg_replace('/\['.LogParser::REGEX_DATETIME_PATTERN.'\][ ]/', '', $header);

        // EXTRACT ENV
        if (preg_match('/^[a-z]+.[A-Z]+:/', $header, $out)) {
            $this->setEnv($out[0]);
            $header = trim(str_replace($out[0], '', $header));
        }

        // EXTRACT CONTEXT
        // Previously used a recursive PCRE (`/{(?:[^{}]|(?R))*}/x`) which
        // silently fails on large/deeply-nested JSON once it hits
        // pcre.backtrack_limit / pcre.recursion_limit. A linear brace scan finds
        // the same first balanced `{...}` in O(n) with no backtracking.
        $json = $this->extractFirstBraceGroup($header);

        if ($json !== null && ! is_null($context = json_decode($json, true))) {
            $header = str_replace($json, '', $header);
            $this->setContext($context);
        }

        return $header;
    }

    /**
     * Find the first balanced `{...}` group in a string via a linear brace scan.
     *
     * Structural (matches the previous recursive regex): returns the substring
     * from the first `{` to its matching `}`, or null when there is none / it is
     * unbalanced. Safe for arbitrarily large or deep input.
     *
     * @param  string  $string
     *
     * @return string|null
     */
    private function extractFirstBraceGroup($string)
    {
        $start = strpos($string, '{');

        if ($start === false) {
            return null;
        }

        $depth = 0;

        for ($i = $start, $len = strlen($string); $i < $len; $i++) {
            $char = $string[$i];

            if ($char === '{') {
                $depth++;
            }
            elseif ($char === '}') {
                $depth--;

                if ($depth === 0) {
                    return substr($string, $start, $i - $start + 1);
                }
            }
        }

        return null; // unbalanced — no complete group
    }

    /**
     * Extract datetime from the header.
     *
     * @param  string  $header
     *
     * @return string
     */
    private function extractDatetime($header)
    {
        return preg_replace('/^\[('.LogParser::REGEX_DATETIME_PATTERN.')\].*/', '$1', $header);
    }
}
