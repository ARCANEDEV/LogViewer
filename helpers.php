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
