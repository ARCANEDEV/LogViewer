<?php

use Arcanedev\LogViewer\Contracts\Utilities\Filesystem;

return [

    /* -----------------------------------------------------------------
     |  Log files storage path
     | -----------------------------------------------------------------
     */

    'storage-path'  => storage_path('logs'),

    /* -----------------------------------------------------------------
     |  Log files pattern
     | -----------------------------------------------------------------
     */

    'pattern'       => [
        'prefix'    => Filesystem::PATTERN_PREFIX,    // 'laravel-'
        'date'      => Filesystem::PATTERN_DATE,      // '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]'
        'extension' => Filesystem::PATTERN_EXTENSION, // '.log'
    ],

    /* -----------------------------------------------------------------
     |  Locale
     | -----------------------------------------------------------------
     |  Supported locales :
     |    'auto', 'ar', 'bg', 'de', 'en', 'es', 'et', 'fa', 'fr', 'hu', 'hy', 'id', 'it', 'ja', 'ko', 'nl',
     |    'pl', 'pt-BR', 'ro', 'ru', 'sv', 'th', 'tr', 'zh-TW', 'zh'
     */

    'locale'        => 'auto',

    /* -----------------------------------------------------------------
     |  Theme
     | -----------------------------------------------------------------
     |  Supported themes :
     |    'observatory'  (analytics-focused, light + dark auto-toggle, risk signals)
     |    'console'      (modern, light + dark auto-toggle, filters)
     |    'bootstrap-5'  (default)
     |    'bootstrap-4'
     |    'bootstrap-3'
     |
     |  You can make your own theme by adding a folder to the views directory and specifying it here.
     |  Set this to 'observatory' or 'console' for the modern UI.
     */

    'theme'         => 'bootstrap-5',

    /* -----------------------------------------------------------------
     |  Route settings
     | -----------------------------------------------------------------
     */

    'route'         => [
        'enabled'    => true,

        'attributes' => [
            'prefix'     => 'log-viewer',

            'middleware' => env('ARCANEDEV_LOGVIEWER_MIDDLEWARE') ? explode(',', env('ARCANEDEV_LOGVIEWER_MIDDLEWARE')) : null,
        ],

        'show' => 'log-viewer::logs.show',
    ],

    /* -----------------------------------------------------------------
     |  Log entries per page
     | -----------------------------------------------------------------
     |  This defines how many logs & entries are displayed per page.
     */

    'per-page'      => 30,

    /* -----------------------------------------------------------------
     |  Type of log formatter used 
     | -----------------------------------------------------------------
     |  Supported formatters :
     |    'LineFormatter', 'JsonFormatter'
     */

    'formatter'     => 'default',

    /* -----------------------------------------------------------------
     |  Streaming index engine
     | -----------------------------------------------------------------
     |  Serves large log files with flat memory by building a binary index
     |  and reading only the entries on the current page. Every option below
     |  defaults to preserving the classic behavior:
     |    - `streaming = false` disables the engine entirely (classic path).
     |    - files smaller than `max-inline-bytes` always use the classic path.
     */

    'streaming'     => [
        'enabled'         => true,

        // Files at or below this size are read the classic (whole-file) way.
        'max-inline-bytes' => 2 * 1024 * 1024, // 2 MB

        // Where to store `.idx` sidecars. null = beside each log file.
        'index-path'      => null,

        // Bounded window (bytes) served while a cold huge file's index warms.
        'tail-bytes'      => 25 * 1024 * 1024, // 25 MB

        // Max index builds allowed across the app at once (the rest serve the
        // tail until a slot frees up).
        'max-build-concurrency' => 2,
    ],

    /* -----------------------------------------------------------------
     |  Download settings
     | -----------------------------------------------------------------
     */

    'download'      => [
        'prefix'    => 'laravel-',

        'extension' => 'log',
    ],

    /* -----------------------------------------------------------------
     |  Delete controls
     | -----------------------------------------------------------------
     |  Show the "delete log file" controls in the `observatory` and
     |  `console` themes. Disabled by default: deleting a log is
     |  destructive and irreversible, and on most installs the viewer is
     |  reachable by anyone who can reach the admin area.
     |
     |  Set to `true` to show the delete buttons, e.g.:
     |    'delete' => (bool) env('LOG_VIEWER_ALLOW_DELETE', false),
     |
     |  Note: this only controls the UI of those two themes. The
     |  `log-viewer::logs.delete` route stays registered (the bootstrap
     |  themes still use it) — protect it with route middleware if you
     |  need to forbid deletion outright.
     */

    'delete'        => false,

    /* -----------------------------------------------------------------
     |  Menu settings
     | -----------------------------------------------------------------
     */

    'menu'  => [
        'filter-route'  => 'log-viewer::logs.filter',

        'icons-enabled' => true,
    ],

    /* -----------------------------------------------------------------
     |  Icons
     | -----------------------------------------------------------------
     */

    'icons' =>  [
        /**
         * Font awesome >= 4.3
         * http://fontawesome.io/icons/
         */
        'all'       => 'fa fa-fw fa-list',                 // http://fontawesome.io/icon/list/
        'emergency' => 'fa fa-fw fa-bug',                  // http://fontawesome.io/icon/bug/
        'alert'     => 'fa fa-fw fa-bullhorn',             // http://fontawesome.io/icon/bullhorn/
        'critical'  => 'fa fa-fw fa-heartbeat',            // http://fontawesome.io/icon/heartbeat/
        'error'     => 'fa fa-fw fa-times-circle',         // http://fontawesome.io/icon/times-circle/
        'warning'   => 'fa fa-fw fa-exclamation-triangle', // http://fontawesome.io/icon/exclamation-triangle/
        'notice'    => 'fa fa-fw fa-exclamation-circle',   // http://fontawesome.io/icon/exclamation-circle/
        'info'      => 'fa fa-fw fa-info-circle',          // http://fontawesome.io/icon/info-circle/
        'debug'     => 'fa fa-fw fa-life-ring',            // http://fontawesome.io/icon/life-ring/
    ],

    /* -----------------------------------------------------------------
     |  Colors
     | -----------------------------------------------------------------
     */

    'colors' =>  [
        'levels'    => [
            'empty'     => '#D1D1D1',
            'all'       => '#8A8A8A',
            'emergency' => '#B71C1C',
            'alert'     => '#D32F2F',
            'critical'  => '#F44336',
            'error'     => '#FF5722',
            'warning'   => '#FF9100',
            'notice'    => '#4CAF50',
            'info'      => '#1976D2',
            'debug'     => '#90CAF9',
        ],
    ],

    /* -----------------------------------------------------------------
     |  Strings to highlight in stack trace
     | -----------------------------------------------------------------
     */

    'highlight' => [
        '^#\d+',
        '^Stack trace:',
    ],

];
