<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Exceptions;

/**
 * Class     LogNotFoundException
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogNotFoundException extends LogViewerException
{
    /**
     * Make the exception.
     *
     * @param  string  $date
     *
     * @return static
     */
    public static function make(string $date)
    {
        return new static("Log not found in this date [{$date}]");
    }
}
