<?php

if ( ! function_exists('log_viewer')) {
    /**
     * Get the LogViewer instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\LogViewerInterface
     */
    function log_viewer() {
        return app('arcanedev.log-viewer');
    }
}

if ( ! function_exists('log_levels')) {
    /**
     * Get the LogLevels instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\LogLevelsInterface
     */
    function log_levels() {
        return app('arcanedev.log-viewer.levels');
    }
}

if ( ! function_exists('log_menu')) {
    /**
     * Get the LogMenu instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\LogMenuInterface
     */
    function log_menu() {
        return app('arcanedev.log-viewer.menu');
    }
}

if ( ! function_exists('log_styler')) {
    /**
     * Get the LogStyler instance.
     *
     * @return \Arcanedev\LogViewer\Contracts\LogStylerInterface
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
