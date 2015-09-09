<?php

if ( ! function_exists('log_viewer')) {
    /**
     * Get the LogViewer instance.
     *
     * @return \Arcanedev\LogViewer\LogViewer
     */
    function log_viewer() {
        return app('arcanedev.log-viewer');
    }
}

if ( ! function_exists('log_menu')) {
    /**
     * Get the LogMenu instance.
     *
     * @return \Arcanedev\LogViewer\Utilities\LogMenu
     */
    function log_menu() {
        return app('arcanedev.log-viewer.menu');
    }
}

if ( ! function_exists('log_styler')) {
    /**
     * Get the LogStyler instance.
     *
     * @return \Arcanedev\LogViewer\Utilities\LogStyler
     */
    function log_styler() {
        return app('arcanedev.log-viewer.styler');
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
        return preg_replace(
            '/.*(' . REGEX_DATE_PATTERN . ').*/', '$1', $string
        );
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
        return preg_replace(
            '/.*(' . REGEX_DATETIME_PATTERN . ').*/', '$1', $string
        );
    }
}
