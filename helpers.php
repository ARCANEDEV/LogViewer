<?php

if ( ! function_exists('log_viewer')) {
    /**
     * Helper to get the LogViewer instance
     *
     * @return \Arcanedev\LogViewer\LogViewer
     */
    function log_viewer() {
        return app('log-viewer');
    }
}

if ( ! function_exists('extract_date')) {
    /**
     * Extract date from string
     *
     * @param  string  $string
     *
     * @return string
     */
    function extract_date($string) {
        return preg_replace('/.*(' . REGEX_DATE_PATTERN . ').*/', '$1', $string);
    }
}

if ( ! function_exists('extract_datetime')) {
    /**
     * Extract date from string
     *
     * @param  string  $string
     *
     * @return string
     */
    function extract_datetime($string) {
        return preg_replace('/.*(' . REGEX_DATETIME_PATTERN . ').*/', '$1', $string);
    }
}
