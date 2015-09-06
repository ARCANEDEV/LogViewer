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

if ( ! function_exists('log_lvl_color')) {
    /**
     * Get Log level color.
     *
     * @param  string  $level
     * @param  string  $default
     *
     * @return mixed
     */
    function log_lvl_colors($level, $default = '') {
        return config('log-viewer.colors.levels.' . $level, $default);
    }
}

if ( ! function_exists('log_lvl_icon')) {
    /**
     * Get Log level color
     *
     * @param  string  $level
     * @param  string  $default
     *
     * @return string
     */
    function log_lvl_icon($level, $default = '') {
        $name = 'log-viewer.icons.' . $level;

        if ( ! config()->has($name)) return '';

        return '<i class="' . config($name, $default) . '"></i>';
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
