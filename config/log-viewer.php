<?php

return [
    /* ------------------------------------------------------------------------------------------------
     |  Locale
     | ------------------------------------------------------------------------------------------------
     | Supported: 'auto', 'ar', 'en', 'fr'
     */
    'locale'         => 'auto',

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
    'menu'          => [
        'icons' => [
            'enabled' => true,

            'levels' =>  [
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
            ]
        ]
    ]
];
