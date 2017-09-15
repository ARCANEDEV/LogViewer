<style>

    html {
        position: relative;
        min-height: 100%;
    }

    body {
        padding-top: 5rem;
        margin-bottom: 80px;
    }

    .footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        height: 60px;
        line-height: 60px;
        background-color: #f5f5f5;
    }

    .stack {
        background-color: #F6F6F6;
        max-width: 0;
        overflow-x: auto;
    }

    .level-card {
        color: #FFF;
    }

    .level-card .progress {
        background: rgba(0,0,0,0.2);
        height: 2px;
        margin-top: 10px;
    }

    .level-card .progress .progress-bar {
        background: #fff;
    }

    .level-card .card-header {
        line-height: 1.5em;
        font-size: 1em;
    }

    .level-card .card-header .level-icon {
        font-size: 1.5em;
    }

    .level-card .card-body {
        font-size: 1em;
    }

    .level {
        font-size: .875em;
        line-height: 1em;
    }

    .level-none {
        background-color: none;
        color: #000;
    }

    .level-all,
    .level-emergency,
    .level-alert,
    .level-critical,
    .level-error,
    .level-warning,
    .level-notice,
    .level-info,
    .level-debug,
    {
        color: #FFF;
    }

    .label-env {
        font-size: .85em;
    }

    .level-all, .level.level-all {
        background-color: {{ log_styler()->color('all') }};
    }

    .level-emergency, .level.level-emergency {
        background-color: {{ log_styler()->color('emergency') }};
    }

    .level-alert, .level.level-alert {
        background-color: {{ log_styler()->color('alert') }};
    }

    .level-critical, .level.level-critical {
        background-color: {{ log_styler()->color('critical') }};
    }

    .level-error, .level.level-error {
        background-color: {{ log_styler()->color('error') }};
    }

    .level-warning, .level.level-warning {
        background-color: {{ log_styler()->color('warning') }};
    }

    .level-notice, .level.level-notice {
        background-color: {{ log_styler()->color('notice') }};
    }

    .level-info, .level.level-info {
        background-color: {{ log_styler()->color('info') }};
    }

    .level-debug, .level.level-debug {
        background-color: {{ log_styler()->color('debug') }};
    }

    .level-empty, .level.level-empty {
        background-color: {{ log_styler()->color('empty') }};
    }

    .label-env, .label.label-env {
        background-color: #6A1B9A;
    }
</style>
