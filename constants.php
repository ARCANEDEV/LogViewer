<?php

if ( ! defined('REGEX_DATE_PATTERN')) {
    define('REGEX_DATE_PATTERN', '\d{4}(-\d{2}){2}'); // YYYY-MM-DD
}

if ( ! defined('REGEX_TIME_PATTERN')) {
    define('REGEX_TIME_PATTERN', '\d{2}(:\d{2}){2}'); // HH:MM:SS
}

if ( ! defined('REGEX_DATETIME_PATTERN')) {
    define(
        'REGEX_DATETIME_PATTERN',
        REGEX_DATE_PATTERN . ' ' . REGEX_TIME_PATTERN // YYYY-MM-DD HH:MM:SS
    );
}

// Fix undefined constant GLOB_BRACE exception on Alpine Linux (https://bugs.php.net/bug.php?id=72095)
if ( ! defined('GLOB_BRACE')) {
    define('GLOB_BRACE', 0);
}
