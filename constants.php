<?php

if (! defined('REGEX_DATE_PATTERN')) {
    define('REGEX_DATE_PATTERN', '\d{4}(?:-\d{2}){2}'); // YYYY-MM-DD
}

if (! defined('REGEX_TIME_PATTERN')) {
    define('REGEX_TIME_PATTERN', '\d{2}(?::\d{2}){2}'); // HH:MM:SS
}

if (! defined('REGEX_DATETIME_SEPARATOR')) {
    define('REGEX_DATETIME_SEPARATOR', '([T ])');
}

if (! defined('REGEX_MS_PATTERN')) {
    define('REGEX_MS_PATTERN', '(\.\d{6})?'); // .uuuuuu
}

if (! defined('REGEX_TIMEZONE_PATTERN')) {
    define('REGEX_TIMEZONE_PATTERN', '([-+]\d{2}:\d{2})?'); // +ZZ:ZZ
}

if (! defined('REGEX_DATETIME_PATTERN')) {
    define(
        'REGEX_DATETIME_PATTERN',
        // YYYY-MM-DDTHH:MM:SS.uuuuuu+ZZ:ZZ
        REGEX_DATE_PATTERN . REGEX_DATETIME_SEPARATOR . REGEX_TIME_PATTERN . REGEX_MS_PATTERN . REGEX_TIMEZONE_PATTERN
    );
}

// Fix undefined constant GLOB_BRACE exception on Alpine Linux (https://bugs.php.net/bug.php?id=72095)
if (! defined('GLOB_BRACE')) {
    define('GLOB_BRACE', 0);
}
