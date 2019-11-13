<?php namespace Arcanedev\LogViewer\Entities;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * Class     LogEntry
 *
 * @package  Arcanedev\LogViewer\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntry implements Arrayable, Jsonable, JsonSerializable
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

    /** @var object */
    public $context;

    /** @var string */
    public $stack;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Construct the log entry instance.
     *
     * @param  string  $level
     * @param  string  $header
     * @param  string  $stack
     */
    public function __construct($level, $header, $stack)
    {
        $this->setLevel($level);
        $this->setHeaderAndContext($header);
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
    private function setHeaderAndContext($header)
    {
        $this->setDatetime($this->extractDatetime($header));

        $header = $this->cleanHeader($header);

        if (preg_match('/^[a-z]+.[A-Z]+:/', $header, $out)) {
            $this->setEnv($out[0]);
            $header = trim(str_replace($out[0], '', $header));
        }

        $this->header = $header;

        $this->context = $this->extractContext($header);

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
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if the entry has a context.
     *
     * @return bool
     */
    public function hasContext()
    {
        return !is_null($this->context);
    }
    
    /**
     * Check if the entry has a stack.
     *
     * @return bool
     */
    public function hasStack()
    {
        return $this->stack !== "\n";
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
        return preg_replace('/\['.REGEX_DATETIME_PATTERN.'\][ ]/', '', $header);
    }

    /**
     * Extract context from the header.
     *
     * @param  string  $header
     *
     * @return object
     */
    private function extractContext($header)
    {
        // Regex from https://stackoverflow.com/a/21995025
        $pattern = '/\{(?:[^{}]|(?R))*\}/x';
        $context = null;
        preg_match_all($pattern, $header, $matches);
        if(!empty($matches) && !empty($matches[0])){
            $context = json_decode($matches[0][0]);
            if(!is_null($context)){
                $this->header = str_replace($matches[0][0], '', $this->header);
            }
        }
        return $context;
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
        return preg_replace('/^\[('.REGEX_DATETIME_PATTERN.')\].*/', '$1', $header);
    }
}
