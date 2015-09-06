<?php

return [
    /* ------------------------------------------------------------------------------------------------
     |  Locale
     | ------------------------------------------------------------------------------------------------
     | Supported: 'auto', 'ar', 'en', 'fr'
     */
    'locale'         => 'auto',

    /* ------------------------------------------------------------------------------------------------
     |  Route
     | ------------------------------------------------------------------------------------------------
     */
    'route'         => [
        'enabled'    => true,

        'attributes' => [
            'prefix'     => 'log-viewer',

            'middleware' => null,
        ],
    ],

    /* ------------------------------------------------------------------------------------------------
     |  Log entries per page
     | ------------------------------------------------------------------------------------------------
     |  This defines how many log entries are displayed per page.
     */
    'per-page'      => 20,

    /* ------------------------------------------------------------------------------------------------
     |  Download settings
     | ------------------------------------------------------------------------------------------------
     */
    'download'      => [
        'prefix'    => 'laravel-',

        'extension' => 'log',
    ],

    /* ------------------------------------------------------------------------------------------------
     |  Menu settings
     | ------------------------------------------------------------------------------------------------
     */
    'menu'  => [
        'icons-enabled' => true,
    ],

    /* ------------------------------------------------------------------------------------------------
     |  Icons
     | ------------------------------------------------------------------------------------------------
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

    /* ------------------------------------------------------------------------------------------------
     |  Colors
     | ------------------------------------------------------------------------------------------------
     */
    'colors' =>  [
        'levels'    => [
            'empty'     => [
                'background' => '#D1D1D1',
                'font'       => "#FFF",
            ],
            'all'       => [
                'background' => '#8A8A8A',
                'font'       => "#FFF",
            ],
            'emergency' => [
                'background' => "#B71C1C",
                'font'       => "#FFF",
            ],
            'alert'     => [
                'background' => "#D32F2F",
                'font'       => "#FFF",
            ],
            'critical'  => [
                'background' => '#F44336',
                'font'       => "#FFF",
            ],
            'error'     => [
                'background' => '#FF5722',
                'font'       => "#FFF",
            ],
            'warning'   => [
                'background' => '#FF9100',
                'font'       => "#FFF",
            ],
            'notice'    => [
                'background' => '#4CAF50',
                'font'       => "#FFF",
            ],
            'info'      => [
                'background' => "#1976D2",
                'font'       => "#FFF",
            ],
            'debug'     => [
                'background' => "#90CAF9",
                'font'       => "#FFF",
            ],
        ],
    ],
];
