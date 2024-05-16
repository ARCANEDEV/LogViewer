<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Entities;

use Arcanedev\LogViewer\Contracts\LogEntryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Support\{Arrayable, Jsonable};
use JsonSerializable;

/**
 * Class     LogEntry
 *
 * @author    iyogesharma <iyogesharma@gmail.com>
 */
class JsonLogEntry implements Arrayable, Jsonable, JsonSerializable, LogEntryInterface
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
    public function __construct($data)
    {
        $this->setLevel($data);
        $this->setHeader($data);
        $this->setStack($data);
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
    private function setLevel($data)
    {
        $this->level = strtolower($data['level_name']);

        return $this;
    }

    /**
     * Set the entry header.
     *
     * @param  string  $header
     *
     * @return self
     */
    private function setHeader($data)
    {
        $this->setDatetime($data['datetime']);

        $header = $data['message'];

        $this->header = trim($header);

        $this->setContext($data['context']);

        $this->setEnv($data['channel']);

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
        $this->datetime = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s',strtotime($datetime)));

        return $this;
    }

    /**
     * Set the entry stack.
     *
     * @param  string  $stack
     *
     * @return self
     */
    private function setStack($data)
    {
        $this->stack = isset($data['stack']) ? json_encode($data['stack']): "";

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
     *
     * @return string
     */
    public function context()
    {
        return json_encode($this->context, JSON_PRETTY_PRINT);
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
}
