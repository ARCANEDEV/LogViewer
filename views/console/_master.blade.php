<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LogViewer">
    <title>@yield('title', 'LogViewer')</title>

    {{-- Apply the saved theme synchronously, before first paint, to avoid a
         light/dark flash (FOUC) on every navigation. --}}
    <script>
        try {
            var t = localStorage.getItem('lv-theme');
            if (t === 'auto' || t === 'light' || t === 'dark') document.documentElement.setAttribute('data-theme', t);
        } catch (e) {}
    </script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        :root {
            --bg: #f6f7f9;        --surface: #ffffff;   --surface-2: #f2f4f7;
            --text: #1a1f2b;      --muted: #667085;     --border: #e7eaf0;
            --brand: #5145cd;     --brand-ink: #ffffff; --shadow: 0 1px 2px rgba(16,24,40,.04), 0 1px 3px rgba(16,24,40,.08);
            --mono: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            --sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            /* level accents — muted, professional (not neon) */
            --lvl-emergency:#912018; --lvl-alert:#b42318; --lvl-critical:#d92d20; --lvl-error:#e0574b;
            --lvl-warning:#c47908;   --lvl-notice:#067647; --lvl-info:#175cd3;    --lvl-debug:#7a8494;
            --lvl-all:#5145cd;
        }
        html[data-theme="dark"] {
            --bg:#0d1017; --surface:#151a22; --surface-2:#1b212b; --text:#e4e8f0; --muted:#98a2b3;
            --border:#252d3a; --brand:#8b88f0; --shadow:0 1px 2px rgba(0,0,0,.4);
            --lvl-emergency:#fda29b; --lvl-alert:#fda29b; --lvl-critical:#fca5a5; --lvl-error:#f4a99f;
            --lvl-warning:#fdb022; --lvl-notice:#6ce9a6; --lvl-info:#84caff; --lvl-debug:#98a2b3;
        }
        @media (prefers-color-scheme: dark) {
            html[data-theme="auto"] {
                --bg:#0d1017; --surface:#151a22; --surface-2:#1b212b; --text:#e4e8f0; --muted:#98a2b3;
                --border:#252d3a; --brand:#8b88f0; --shadow:0 1px 2px rgba(0,0,0,.4);
                --lvl-emergency:#fda29b; --lvl-alert:#fda29b; --lvl-critical:#fca5a5; --lvl-error:#f4a99f;
                --lvl-warning:#fdb022; --lvl-notice:#6ce9a6; --lvl-info:#84caff; --lvl-debug:#98a2b3;
            }
        }

        * { box-sizing: border-box; }
        html, body { margin:0; padding:0; }
        body { background:var(--bg); color:var(--text); font-family:var(--sans); font-size:14px; line-height:1.5; -webkit-font-smoothing:antialiased; }
        a { color:var(--brand); text-decoration:none; }
        a:hover { text-decoration:underline; }
        code, pre, .mono { font-family:var(--mono); }

        /* Top bar */
        .topbar { position:sticky; top:0; z-index:50; display:flex; align-items:center; gap:18px;
            background:var(--surface); border-bottom:1px solid var(--border); padding:0 20px; height:56px; }
        .topbar .brand { font-weight:700; font-size:15px; letter-spacing:.2px; display:flex; align-items:center; gap:8px; color:var(--text); }
        .topbar .brand .dot { width:9px; height:9px; border-radius:50%; background:var(--brand); box-shadow:0 0 0 3px color-mix(in srgb, var(--brand) 25%, transparent); }
        .topbar nav { display:flex; gap:4px; }
        .topbar nav a { color:var(--muted); padding:7px 12px; border-radius:8px; font-weight:500; }
        .topbar nav a:hover { background:var(--surface-2); text-decoration:none; color:var(--text); }
        .topbar nav a.active { color:var(--text); background:var(--surface-2); }
        .topbar .spacer { flex:1; }
        .icon-btn { background:transparent; border:1px solid var(--border); color:var(--muted); width:36px; height:36px;
            border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-size:15px; }
        .icon-btn:hover { color:var(--text); background:var(--surface-2); }

        .wrap { max-width:1400px; margin:0 auto; padding:22px 20px 60px; }
        .page-title { font-size:20px; font-weight:700; margin:0 0 2px; }
        .page-sub { color:var(--muted); margin:0 0 18px; font-size:13px; }

        .card { background:var(--surface); border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow); }
        .card + .card { margin-top:16px; }
        .card-head { padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .card-title { font-weight:600; font-size:13px; text-transform:uppercase; letter-spacing:.4px; color:var(--muted); }

        /* Buttons */
        .btn { display:inline-flex; align-items:center; gap:6px; padding:7px 12px; border-radius:8px; border:1px solid var(--border);
            background:var(--surface); color:var(--text); font-size:13px; font-weight:500; cursor:pointer; transition:.12s; }
        .btn:hover { background:var(--surface-2); text-decoration:none; }
        .btn-primary { background:var(--brand); border-color:var(--brand); color:var(--brand-ink); }
        .btn-primary:hover { filter:brightness(1.06); background:var(--brand); }
        .btn-danger { color:#fff; background:#dc2626; border-color:#dc2626; }
        .btn-danger:hover { background:#b91c1c; }
        .btn-sm { padding:5px 9px; font-size:12px; }

        /* Level chips */
        .chip { display:inline-flex; align-items:center; gap:6px; padding:3px 9px; border-radius:999px; font-size:12px;
            font-weight:600; border:1px solid var(--border); background:var(--surface-2); color:var(--text); white-space:nowrap; }
        .chip .n { opacity:.7; font-variant-numeric:tabular-nums; }
        .chip.lvl { border-color:color-mix(in srgb, var(--c) 45%, transparent); color:var(--c);
            background:color-mix(in srgb, var(--c) 12%, transparent); }
        .chip.lvl .fa { color:var(--c); }
        .lvl-emergency{--c:var(--lvl-emergency)} .lvl-alert{--c:var(--lvl-alert)} .lvl-critical{--c:var(--lvl-critical)}
        .lvl-error{--c:var(--lvl-error)} .lvl-warning{--c:var(--lvl-warning)} .lvl-notice{--c:var(--lvl-notice)}
        .lvl-info{--c:var(--lvl-info)} .lvl-debug{--c:var(--lvl-debug)} .lvl-all{--c:var(--lvl-all)}

        .chipbar { display:flex; flex-wrap:wrap; gap:8px; }
        a.chip { transition:.12s; }
        a.chip:hover { text-decoration:none; filter:brightness(1.04); border-color:var(--c); }
        a.chip.active { outline:2px solid var(--c); outline-offset:1px; }
        .chip.muted { opacity:.45; }

        /* Inputs */
        .input, select.input { background:var(--surface); border:1px solid var(--border); color:var(--text);
            border-radius:8px; padding:8px 11px; font-size:13px; font-family:inherit; outline:none; }
        .input:focus, select.input:focus { border-color:var(--brand); box-shadow:0 0 0 3px color-mix(in srgb, var(--brand) 20%, transparent); }
        .search-wrap { position:relative; flex:1; min-width:180px; }
        .search-wrap .fa { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--muted); }
        .search-wrap .input { width:100%; padding-left:32px; }

        /* Meta row */
        .meta { display:flex; flex-wrap:wrap; gap:22px; padding:12px 16px; color:var(--muted); font-size:12.5px; }
        .meta b { color:var(--text); font-weight:600; }
        .meta .mono { font-size:12px; }

        /* Log console */
        .logline { display:grid; grid-template-columns:76px 116px minmax(0,1fr) auto; align-items:start; gap:12px;
            padding:9px 16px; border-bottom:1px solid var(--border); cursor:default; }
        .logline:hover { background:var(--surface-2); }
        .logline > * { min-width:0; }
        .logline .time { font-family:var(--mono); font-size:12px; color:var(--muted); padding-top:3px; white-space:nowrap; }
        .logline .lvlcol { padding-top:1px; }
        .logline .msg { font-family:var(--mono); font-size:12.5px; white-space:pre-wrap; overflow-wrap:anywhere; word-break:break-word;
            border-left:3px solid var(--c, var(--border)); padding-left:10px; max-width:100%; }
        .logline .env { font-size:11px; color:var(--muted); }
        .logline .acts { display:flex; gap:6px; opacity:0; transition:.12s; }
        .logline:hover .acts { opacity:1; }
        .logline.open .acts { opacity:1; }
        .panel { grid-column:1 / -1; margin-top:8px; display:none; }
        .logline.open .panel { display:block; }
        .panel pre { background:var(--surface-2); border:1px solid var(--border); border-radius:8px; padding:12px;
            overflow:auto; font-size:12px; margin:6px 0 0; max-height:420px; white-space:pre-wrap; overflow-wrap:anywhere; }
        .panel .panel-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:var(--muted); margin-top:12px;
            display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .panel .panel-label .fa { margin-right:4px; }
        .panel .panel-meta { display:flex; align-items:center; gap:8px; font-size:11.5px; color:var(--muted); margin-bottom:2px; }
        .panel .panel-meta .copy { margin-left:auto; }
        .btn.copy.ok { color:var(--lvl-notice); border-color:color-mix(in srgb, var(--lvl-notice) 45%, transparent); }
        .logline.is-expandable .expand:hover .msg { text-decoration:underline dotted; text-underline-offset:3px; }
        .disc { color:var(--muted); width:14px; display:inline-block; transition:.12s; }
        .logline.open .disc { transform:rotate(90deg); }
        .expand { background:none; border:none; color:var(--text); cursor:pointer; font-family:var(--mono); font-size:12.5px;
            text-align:left; padding:0; display:flex; gap:6px; align-items:flex-start; width:100%; min-width:0; }
        .expand .msg { min-width:0; flex:1; }

        .empty-state { text-align:center; color:var(--muted); padding:48px 16px; }
        .toolbar { display:flex; gap:10px; align-items:center; flex-wrap:wrap; padding:12px 16px; border-bottom:1px solid var(--border); }
        .pager { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; color:var(--muted); font-size:13px; }
        .pager .pages { display:flex; gap:8px; }

        table.grid { width:100%; border-collapse:collapse; }
        table.grid th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--muted);
            padding:10px 14px; border-bottom:1px solid var(--border); }
        table.grid td { padding:9px 14px; border-bottom:1px solid var(--border); font-size:13px; }
        table.grid tr:hover td { background:var(--surface-2); }
        .num { font-variant-numeric:tabular-nums; }
        .stat-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px,1fr)); gap:14px; padding:16px; }
        .stat { border:1px solid var(--border); border-radius:12px; padding:14px 16px; background:var(--surface);
            border-left:4px solid var(--c, var(--border)); }
        .stat .k { color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.4px; }
        .stat .v { font-size:24px; font-weight:700; margin-top:4px; font-variant-numeric:tabular-nums; }
        .stat .p { color:var(--muted); font-size:12px; }
        .streaming-badge { font-size:11px; color:var(--lvl-info); border:1px solid color-mix(in srgb,var(--lvl-info) 40%,transparent);
            background:color-mix(in srgb,var(--lvl-info) 12%,transparent); padding:2px 8px; border-radius:999px; }

        /* ---- Dashboard analytics ---- */
        .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(190px,1fr)); gap:14px; margin-bottom:16px; }
        .kpi { background:var(--surface); border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow);
            padding:16px 18px; display:flex; flex-direction:column; gap:2px; position:relative; overflow:hidden; }
        .kpi .kpi-ico { position:absolute; right:14px; top:14px; width:34px; height:34px; border-radius:9px;
            display:flex; align-items:center; justify-content:center; font-size:15px;
            color:var(--c,var(--brand)); background:color-mix(in srgb, var(--c,var(--brand)) 12%, transparent); }
        .kpi .kpi-label { color:var(--muted); font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; }
        .kpi .kpi-value { font-size:28px; font-weight:700; font-variant-numeric:tabular-nums; line-height:1.1; margin-top:6px;
            color:var(--c, var(--text)); }
        .kpi .kpi-sub { color:var(--muted); font-size:12px; margin-top:2px; }

        .dash-grid { display:grid; grid-template-columns:1.6fr 1fr; gap:16px; align-items:stretch; }
        @media (max-width: 900px) { .dash-grid { grid-template-columns:1fr; } }
        .chart-card { background:var(--surface); border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow);
            display:flex; flex-direction:column; min-width:0; }
        .chart-card .ch-head { padding:14px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .chart-card .ch-title { font-weight:600; font-size:13px; }
        .chart-card .ch-body { padding:16px; flex:1; display:flex; align-items:center; justify-content:center; min-height:240px; position:relative; }
        .chart-card canvas { max-width:100%; }

        /* Level breakdown list (dashboard) */
        .lvl-list { display:flex; flex-direction:column; }
        .lvl-row { display:grid; grid-template-columns:150px 1fr 76px 56px; align-items:center; gap:12px;
            padding:11px 16px; border-top:1px solid var(--border); }
        .lvl-row:first-child { border-top:none; }
        .lvl-row .name { display:flex; align-items:center; gap:8px; font-weight:600; font-size:13px; color:var(--c,var(--text)); }
        .lvl-row .bar { height:8px; border-radius:999px; background:var(--surface-2); overflow:hidden; }
        .lvl-row .bar > i { display:block; height:100%; border-radius:999px; background:var(--c,var(--brand)); }
        .lvl-row .ct { text-align:right; font-variant-numeric:tabular-nums; font-weight:600; font-size:13px; }
        .lvl-row .pc { text-align:right; font-variant-numeric:tabular-nums; color:var(--muted); font-size:12px; }
        @media (max-width: 560px) { .lvl-row { grid-template-columns:110px 1fr 60px; } .lvl-row .pc { display:none; } }

        /* Severity composition bar (logs table) */
        .sev-bar { display:flex; height:8px; border-radius:999px; overflow:hidden; background:var(--surface-2); min-width:90px; }
        .sev-bar > span { display:block; height:100%; }
        .sev-legend { display:inline-flex; align-items:center; gap:5px; font-size:11px; color:var(--muted); }
        .sev-legend .dot { width:8px; height:8px; border-radius:2px; display:inline-block; }
        td .cell-num { font-variant-numeric:tabular-nums; font-weight:600; }


        /* ---- Dimensions terminal (dynamic, content-derived filters) ----
           Deliberately styled as a terminal window — traffic-light dots, a
           prompt gutter, monospace `key: token ×N` rows — so the console theme
           reads nothing like the observatory card grid. Chips run a whole-file
           server search via the existing search form. */
        .term { background:var(--surface); border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow);
            overflow:hidden; margin-top:16px; }
        .term-head { display:flex; align-items:center; gap:10px; padding:9px 14px; border-bottom:1px solid var(--border);
            background:color-mix(in srgb, var(--surface-2) 70%, var(--surface)); }
        .term-dots { display:inline-flex; gap:6px; }
        .term-dots i { width:11px; height:11px; border-radius:50%; display:inline-block; }
        .term-dots i:nth-child(1){ background:#ec6a5e; } .term-dots i:nth-child(2){ background:#f4bf4f; } .term-dots i:nth-child(3){ background:#61c554; }
        .term-title { font-family:var(--mono); font-size:12px; color:var(--muted); }
        .term-title b { color:var(--text); font-weight:600; }
        .term-count { margin-left:auto; font-family:var(--mono); font-size:11.5px; color:var(--muted); }
        .term-tools { display:flex; align-items:center; gap:10px; padding:9px 14px; border-bottom:1px solid var(--border); flex-wrap:wrap; }
        .term-prompt { position:relative; flex:1; min-width:200px; display:flex; align-items:center; gap:8px;
            font-family:var(--mono); color:var(--muted); }
        .term-prompt .caret { color:var(--lvl-notice); font-weight:700; }
        .term-prompt input { flex:1; background:transparent; border:none; outline:none; color:var(--text);
            font-family:var(--mono); font-size:12.5px; padding:4px 0; }
        .term-body { padding:2px 0; max-height:280px; overflow:auto; }
        .term-body.expanded { max-height:none; }
        .dim-row { display:grid; grid-template-columns:132px minmax(0,1fr); gap:12px; align-items:baseline;
            padding:7px 14px; border-top:1px dashed var(--border); }
        .dim-row:first-child { border-top:none; }
        .dim-key { font-family:var(--mono); font-size:12px; color:var(--brand); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .dim-key .fa { color:var(--muted); margin-right:5px; }
        .dim-vals { display:flex; flex-wrap:wrap; gap:6px; min-width:0; }
        .token { display:inline-flex; align-items:center; gap:6px; font-family:var(--mono); font-size:12px; max-width:100%;
            padding:2px 8px; border-radius:6px; border:1px solid var(--border); background:var(--surface-2); color:var(--text);
            cursor:pointer; transition:.12s; }
        .token .lbl { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:320px; }
        .token .n { color:var(--muted); font-size:11px; font-variant-numeric:tabular-nums; }
        .token:hover { border-color:var(--brand); color:var(--brand); }
        .token:hover .n { color:color-mix(in srgb, var(--brand) 70%, var(--muted)); }
        .token.active { background:color-mix(in srgb, var(--brand) 16%, transparent); border-color:var(--brand); color:var(--brand); }
        .dim-row[hidden] { display:none; }
        .term-empty { padding:20px 14px; font-family:var(--mono); font-size:12.5px; color:var(--muted); }

        /* Active whole-file pattern indicator (in the primary toolbar) */
        .active-pattern { display:none; align-items:center; gap:7px; font-family:var(--mono); font-size:12px;
            padding:4px 10px; border-radius:8px; border:1px solid var(--brand);
            background:color-mix(in srgb, var(--brand) 12%, transparent); color:var(--brand); }
        .active-pattern.show { display:inline-flex; }
        .active-pattern .x { background:none; border:none; color:inherit; cursor:pointer; padding:0; font-size:13px; line-height:1; }

        #group-toggle.active { background:color-mix(in srgb, var(--brand) 14%, transparent); border-color:var(--brand); color:var(--brand); }
        .filter-count { font-size:12px; color:var(--muted); white-space:nowrap; font-variant-numeric:tabular-nums; }
        .count-badge { font-size:11px; font-weight:700; font-variant-numeric:tabular-nums; color:var(--muted);
            background:var(--surface-2); border:1px solid var(--border); border-radius:999px; padding:1px 7px; margin-left:8px; }
        .logline:not(.grouped) .count-badge { display:none; }

        /* ---- Two-column layout: sticky filter rail + log stream ---- */
        .lv-layout { display:grid; grid-template-columns:300px minmax(0,1fr); gap:16px; align-items:start; margin-top:16px; }
        .lv-side { position:sticky; top:72px; display:flex; flex-direction:column; gap:12px; }
        .lv-main { min-width:0; }
        .lv-main .card:first-child { margin-top:0; }
        @media (max-width:1000px) {
            .lv-layout { grid-template-columns:1fr; }
            .lv-side { position:static; }
        }

        /* Sidebar variant of the dimensions terminal (vertical, narrow) */
        .lv-side .term { margin-top:0; display:flex; flex-direction:column; max-height:calc(100vh - 96px); }
        .lv-side .term-body { max-height:none; flex:1; }
        .lv-side .dim-row { grid-template-columns:1fr; gap:5px; padding:9px 14px; }
        .lv-side .dim-key { font-size:11px; text-transform:uppercase; letter-spacing:.4px; }
        .lv-side .token { max-width:100%; }
        .lv-side .token .lbl { max-width:210px; }
        .lv-side .term-count { display:none; }

        /* Severity facet list (sidebar) */
        .side-levels { display:flex; flex-direction:column; }
        .side-levels a { display:flex; align-items:center; gap:8px; padding:8px 14px; border-top:1px solid var(--border);
            color:var(--text); font-size:12.5px; }
        .side-levels a:first-child { border-top:none; }
        .side-levels a:hover { background:var(--surface-2); text-decoration:none; }
        .side-levels a.active { background:color-mix(in srgb, var(--c,var(--brand)) 12%, transparent);
            box-shadow:inset 3px 0 0 var(--c,var(--brand)); }
        .side-levels a.muted { opacity:.45; }
        .side-levels .dot { width:8px; height:8px; border-radius:50%; background:var(--c,var(--brand)); flex:none; }
        .side-levels .nm { flex:1; }
        .side-levels .n { color:var(--muted); font-variant-numeric:tabular-nums; }
        .side-meta { padding:12px 14px; font-size:12px; color:var(--muted); display:flex; flex-direction:column; gap:6px; }
        .side-meta b { color:var(--text); font-weight:600; }
        .side-sub { padding:9px 14px; font-family:var(--mono); font-size:11px; text-transform:uppercase; letter-spacing:.5px;
            color:var(--muted); border-bottom:1px solid var(--border); background:color-mix(in srgb, var(--surface-2) 60%, var(--surface)); }

        /* ---- Loading indicators (server-side filter navigation) ---- */
        @keyframes lv-spin { to { transform:rotate(360deg); } }
        .lv-spinner { display:inline-block; width:12px; height:12px; border:2px solid color-mix(in srgb, currentColor 28%, transparent);
            border-top-color:currentColor; border-radius:50%; animation:lv-spin .6s linear infinite; flex:none; }
        .is-loading { pointer-events:none; opacity:.7; }
        #lv-progress { position:fixed; top:0; left:0; height:2px; width:0; background:var(--brand);
            box-shadow:0 0 8px color-mix(in srgb, var(--brand) 60%, transparent); z-index:200; opacity:0; }
        #lv-progress.active { opacity:1; width:92%; transition:width 9s cubic-bezier(.05,.7,.1,1); }
        #lv-progress.done { opacity:0; width:100%; transition:width .2s ease, opacity .3s ease .1s; }

        /* ---- Expand panel: context-only ---- */
        .panel-bar { display:flex; align-items:center; gap:10px; margin-top:10px; padding-bottom:9px; border-bottom:1px solid var(--border); }
        .panel-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:var(--muted); font-weight:600; }
        .panel-label .fa { margin-right:5px; }
        .panel-bar .panel-meta { margin-left:auto; font-size:11.5px; color:var(--muted); }
        .btn.ok { color:var(--lvl-notice); border-color:color-mix(in srgb, var(--lvl-notice) 45%, transparent); }
        /* Context JSON viewer */
        .panel pre.ctx-json { background:color-mix(in srgb, var(--surface-2) 70%, var(--surface)); border:1px solid var(--border);
            border-radius:8px; padding:14px 16px; margin:10px 0 0; font-family:var(--mono); font-size:12.5px; line-height:1.65;
            overflow:auto; max-height:460px; white-space:pre; tab-size:2; }
        .ctx-json .jk { color:var(--lvl-info); }
        .ctx-json .js { color:var(--lvl-notice); }
        .ctx-json .jn { color:var(--lvl-warning); }
        .ctx-json .jb { color:var(--brand); font-weight:600; }
        .ctx-json .jz { color:var(--muted); font-style:italic; }

        /* Modal */
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); display:none; align-items:center; justify-content:center; z-index:100; }
        .modal-overlay.show { display:flex; }
        .modal { background:var(--surface); border:1px solid var(--border); border-radius:12px; width:440px; max-width:92vw; box-shadow:0 12px 40px rgba(0,0,0,.3); }
        .modal h3 { margin:0; padding:16px; border-bottom:1px solid var(--border); font-size:15px; }
        .modal .body { padding:16px; color:var(--muted); }
        .modal .foot { padding:14px 16px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--border); }

    </style>
</head>
<body>
    <div id="lv-progress"></div>
    @php $current = request()->route() ? request()->route()->getName() : ''; @endphp
    <header class="topbar">
        <a href="{{ route('log-viewer::dashboard') }}" class="brand"><span class="dot"></span> LogViewer</a>
        <nav>
            <a href="{{ route('log-viewer::dashboard') }}" class="{{ str_contains($current,'dashboard') ? 'active' : '' }}">
                <i class="fa fa-tachometer"></i> @lang('Dashboard')
            </a>
            <a href="{{ route('log-viewer::logs.list') }}" class="{{ str_contains($current,'logs.list') || str_contains($current,'logs.show') ? 'active' : '' }}">
                <i class="fa fa-list"></i> @lang('Logs')
            </a>
        </nav>
        <span class="spacer"></span>
        <button class="icon-btn" id="theme-toggle" title="Toggle theme" aria-label="Toggle theme"><i class="fa fa-moon-o"></i></button>
    </header>

    <main class="wrap">
        @yield('content')
    </main>

    @yield('modals')

    <script>
        function ready(fn){ document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn); }

        // Signal that a server-side navigation (filter / search / page) is in flight:
        // a thin top progress bar, plus an optional inline spinner on the clicked control.
        window.lvStartLoading = function (el) {
            var bar = document.getElementById('lv-progress');
            if (bar) { bar.classList.remove('done'); bar.classList.remove('active'); void bar.offsetWidth; bar.classList.add('active'); }
            if (el && el.tagName !== 'FORM' && !el.classList.contains('is-loading')) {
                el.classList.add('is-loading');
                el.setAttribute('aria-busy', 'true');
                if (!el.querySelector('.lv-spinner')) {
                    var s = document.createElement('span');
                    s.className = 'lv-spinner';
                    el.insertBefore(s, el.firstChild);
                }
            }
        };
        // Restore a clean state if the page is served from the back/forward cache.
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            var bar = document.getElementById('lv-progress');
            if (bar) bar.classList.remove('active', 'done');
            document.querySelectorAll('.is-loading').forEach(function (x) {
                x.classList.remove('is-loading'); x.removeAttribute('aria-busy');
                var sp = x.querySelector('.lv-spinner'); if (sp) sp.remove();
            });
        });

        // Theme: auto (system) -> light -> dark, persisted.
        (function () {
            const root = document.documentElement;
            const saved = localStorage.getItem('lv-theme');
            if (saved) root.setAttribute('data-theme', saved);
            function paint() {
                const t = root.getAttribute('data-theme');
                const dark = t === 'dark' || (t === 'auto' && matchMedia('(prefers-color-scheme: dark)').matches);
                const i = document.querySelector('#theme-toggle i');
                if (i) i.className = dark ? 'fa fa-sun-o' : 'fa fa-moon-o';
            }
            ready(function () {
                paint();
                document.getElementById('theme-toggle').addEventListener('click', function () {
                    const order = ['auto', 'light', 'dark'];
                    const next = order[(order.indexOf(root.getAttribute('data-theme')) + 1) % order.length];
                    root.setAttribute('data-theme', next);
                    localStorage.setItem('lv-theme', next);
                    paint();
                });
                matchMedia('(prefers-color-scheme: dark)').addEventListener('change', paint);
            });
        })();
    </script>
    @yield('scripts')
</body>
</html>
