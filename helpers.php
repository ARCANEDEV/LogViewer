<?php

if ( ! function_exists('log_viewer')) {
    /**
     * Get the LogViewer instance.
     *
     * @return \Arcanedev\LogViewer\LogViewer
     */
    function log_viewer() {
        return app('log-viewer');
    }
}

if ( ! function_exists('extract_date')) {
    /**
     * Extract date from string (format : YYYY-MM-DD).
     *
     * @param  string  $string
     *
     * @return string
     */
    function extract_date($string) {
        $pattern = '/.*(' . REGEX_DATE_PATTERN . ').*/';

        return preg_replace($pattern, '$1', $string);
    }
}

if ( ! function_exists('extract_datetime')) {
    /**
     * Extract date and time from string (format : YYYY-MM-DD HH:MM:SS).
     *
     * @param  string  $string
     *
     * @return string
     */
    function extract_datetime($string) {
        $pattern = '/.*(' . REGEX_DATETIME_PATTERN . ').*/';

        return preg_replace($pattern, '$1', $string);
    }
}
