<style>
    html {
        position: relative;
        min-height: 100%;
    }

    body {
        padding-top: 50px;
        /* Margin bottom by footer height */
        margin-bottom: 50px;
        font-family: 'Source Sans Pro';
        font-weight: 600;
    }

    h1, h2, h3 {
        font-family: 'Montserrat';
    }

    .sub-header {
        padding-bottom: 10px;
        border-bottom: 1px solid #EEE;
    }

    .navbar-inverse {
        background-color: #1a237e;
        border-color: #283593;
    }

    .navbar-inverse .navbar-brand {
        color: #c5cae9;
    }

    .navbar-inverse .navbar-nav>li>a {
        color: #c5cae9;
    }

    .navbar-fixed-top {
        border: 0;
    }

    .sidebar {
        margin-top: 20px;
        padding: 20px;
        border: 1px solid #eee;
        background-color: #F5F5F5;
    }

    .nav-sidebar {
        margin: 0 -21px; /* 20px padding + 1px border */
    }

    .nav-sidebar > li > a {
        padding-right: 20px;
        padding-left: 20px;
    }

    .nav-sidebar > .active > a,
    .nav-sidebar > .active > a:hover,
    .nav-sidebar > .active > a:focus {
        color: #fff;
        background-color: #428BCA;
    }

    .main {
        padding: 20px;
    }

    .main .page-header {
        margin-top: 0;
    }

    footer.main-footer {
        position: absolute;
        padding: 10px 0;
        bottom: 0;
        width: 100%;
        background-color: #e8eaf6;
        font-weight: 600;
    }

    footer.main-footer p {
        margin: 0;
    }

    footer.main-footer i.fa.fa-heart {
        color: #C62828;
    }

    /* Log level labels */
    span.level {
        padding: 2px 6px;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
        border-radius: 2px;
        font-size: .9em;
        font-weight: 600;
    }

    .progress {
        margin-bottom: 10px;
    }

    span.level.level-empty,
    span.level.level-empty i {
        background-color: {{ log_lvl_color('empty.background') }};
        color: {{ log_lvl_color('empty.font') }};
    }

    .progress-bar.level-all,
    span.level.level-all,
    span.level.level-all i {
        background-color: {{ log_lvl_color('all.background') }};
        color: {{ log_lvl_color('all.font') }};
    }

    .progress-bar.level-emergency,
    span.level.level-emergency,
    span.level.level-emergency i {
        background-color: {{ log_lvl_color('emergency.background') }};
        color: {{ log_lvl_color('emergency.font') }};
    }

    .progress-bar.level-alert,
    span.level.level-alert,
    span.level.level-alert i {
        background-color: {{ log_lvl_color('alert.background') }};
        color: {{ log_lvl_color('alert.font') }};
    }

    .progress-bar.level-critical,
    span.level.level-critical,
    span.level.level-critical i {
        background-color: {{ log_lvl_color('critical.background') }};
        color: {{ log_lvl_color('critical.font') }};
    }

    .progress-bar.level-error,
    span.level.level-error,
    span.level.level-error i {
        background-color: {{ log_lvl_color('error.background') }};
        color: {{ log_lvl_color('error.font') }};
    }

    .progress-bar.level-warning,
    span.level.level-warning,
    span.level.level-warning i {
        background-color: {{ log_lvl_color('warning.background') }};
        color: {{ log_lvl_color('warning.font') }};
    }

    .progress-bar.level-notice,
    span.level.level-notice,
    span.level.level-notice i {
        background-color: {{ log_lvl_color('notice.background') }};
        color: {{ log_lvl_color('notice.font') }};
    }

    .progress-bar.level-info,
    span.level.level-info,
    span.level.level-info i {
        background-color: {{ log_lvl_color('info.background') }};
        color: {{ log_lvl_color('info.font') }};
    }

    .progress-bar.level-debug,
    span.level.level-debug,
    span.level.level-debug i {
        background-color: {{ log_lvl_color('debug.background') }};
        color: {{ log_lvl_color('debug.font') }};
    }
</style>
