<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LogViewer">
    <title>@yield('title', 'LogViewer')</title>

    {{-- Apply the persisted theme before CSS paints to prevent a light/dark flash during navigation. --}}
    <script>
        try {
            var storedTheme = localStorage.getItem('lv-theme');
            if (storedTheme === 'auto' || storedTheme === 'light' || storedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', storedTheme);
            }
        } catch (e) {}
    </script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        :root {
            --bg: #f5f6f8;        --surface: #ffffff;   --surface-2: #f0f2f5;
            --text: #1c2333;      --muted: #6b7280;     --border: #e4e7ec;
            --brand: #4f46e5;     --brand-ink: #ffffff; --shadow: 0 1px 2px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.1);
            --mono: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            --sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            /* level accents */
            --lvl-emergency:#b0060f; --lvl-alert:#c81e1e; --lvl-critical:#dc2626; --lvl-error:#ef4444;
            --lvl-warning:#d97706;   --lvl-notice:#2563eb; --lvl-info:#0891b2;    --lvl-debug:#6b7280;
            --lvl-all:#4f46e5;
        }
        html[data-theme="dark"] {
            --bg:#0e1116; --surface:#161b22; --surface-2:#1c222b; --text:#e6e9ef; --muted:#9aa4b2;
            --border:#242c37; --brand:#7c83ff; --shadow:0 1px 2px rgba(0,0,0,.4);
            --lvl-emergency:#f87171; --lvl-alert:#f87171; --lvl-critical:#fb7185; --lvl-error:#f87171;
            --lvl-warning:#fbbf24; --lvl-notice:#60a5fa; --lvl-info:#22d3ee; --lvl-debug:#94a3b8;
        }
        @media (prefers-color-scheme: dark) {
            html[data-theme="auto"] {
                --bg:#0e1116; --surface:#161b22; --surface-2:#1c222b; --text:#e6e9ef; --muted:#9aa4b2;
                --border:#242c37; --brand:#7c83ff; --shadow:0 1px 2px rgba(0,0,0,.4);
                --lvl-emergency:#f87171; --lvl-alert:#f87171; --lvl-critical:#fb7185; --lvl-error:#f87171;
                --lvl-warning:#fbbf24; --lvl-notice:#60a5fa; --lvl-info:#22d3ee; --lvl-debug:#94a3b8;
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
        .logline { display:grid; grid-template-columns:78px 108px 1fr auto; align-items:start; gap:12px;
            padding:8px 16px; border-bottom:1px solid var(--border); cursor:default; }
        .logline:hover { background:var(--surface-2); }
        .logline .time { font-family:var(--mono); font-size:12px; color:var(--muted); padding-top:2px; }
        .logline .lvlcol { padding-top:1px; }
        .logline .msg { font-family:var(--mono); font-size:12.5px; white-space:pre-wrap; word-break:break-word;
            border-left:3px solid var(--c, var(--border)); padding-left:10px; }
        .logline .env { font-size:11px; color:var(--muted); }
        .logline .acts { display:flex; gap:6px; opacity:0; transition:.12s; }
        .logline:hover .acts { opacity:1; }
        .logline.open .acts { opacity:1; }
        .panel { grid-column:1 / -1; margin-top:8px; display:none; }
        .logline.open .panel { display:block; }
        .panel pre { background:var(--surface-2); border:1px solid var(--border); border-radius:8px; padding:12px;
            overflow:auto; font-size:12px; margin:6px 0 0; max-height:420px; }
        .panel .panel-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:var(--muted); margin-top:8px; }
        .panel .copy { float:right; }
        .disc { color:var(--muted); width:14px; display:inline-block; transition:.12s; }
        .logline.open .disc { transform:rotate(90deg); }
        .expand { background:none; border:none; color:var(--text); cursor:pointer; font-family:var(--mono); font-size:12.5px;
            text-align:left; padding:0; display:flex; gap:6px; align-items:flex-start; width:100%; }

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


        /* Modal */
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); display:none; align-items:center; justify-content:center; z-index:100; }
        .modal-overlay.show { display:flex; }
        .modal { background:var(--surface); border:1px solid var(--border); border-radius:12px; width:440px; max-width:92vw; box-shadow:0 12px 40px rgba(0,0,0,.3); }
        .modal h3 { margin:0; padding:16px; border-bottom:1px solid var(--border); font-size:15px; }
        .modal .body { padding:16px; color:var(--muted); }
        .modal .foot { padding:14px 16px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--border); }

        /* Observatory: a cool slate/teal operations palette, deliberately separate from severity. */
        :root, html[data-theme="light"] {
            --bg:#f2f5f6; --surface:#fff; --surface-2:#edf2f3; --text:#14262c; --muted:#657a82;
            --border:#d8e2e5; --brand:#087f8c; --brand-ink:#fff; --shadow:0 1px 2px rgba(15,38,46,.05),0 10px 28px rgba(15,38,46,.04);
            --grid:rgba(45,75,84,.12); --code:#f6f8f9;
        }
        html[data-theme="dark"] {
            --bg:#071217; --surface:#0e1b21; --surface-2:#14252c; --text:#e2edef; --muted:#91a7af;
            --border:#21363e; --brand:#35c2c9; --brand-ink:#062126; --shadow:0 1px 2px rgba(0,0,0,.4);
            --grid:rgba(155,190,200,.12); --code:#09151a;
        }
        @media (prefers-color-scheme:dark) {
            html[data-theme="auto"] {
                --bg:#071217; --surface:#0e1b21; --surface-2:#14252c; --text:#e2edef; --muted:#91a7af;
                --border:#21363e; --brand:#35c2c9; --brand-ink:#062126; --shadow:0 1px 2px rgba(0,0,0,.4);
                --grid:rgba(155,190,200,.12); --code:#09151a;
            }
        }
        html,body { max-width:100%; } body { overflow-x:hidden; }
        .topbar { backdrop-filter:blur(14px); background:color-mix(in srgb,var(--surface) 92%,transparent); }
        .wrap { max-width:1480px; padding-top:26px; }
        .card { border-radius:10px; }
        .summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:16px; }
        .summary { padding:17px 18px; position:relative; overflow:hidden; }
        .summary .label { color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.65px; }
        .summary .value { margin-top:7px; font-size:27px; line-height:1.2; font-weight:750; letter-spacing:-.6px; font-variant-numeric:tabular-nums; }
        .summary .hint { margin-top:4px; color:var(--muted); font-size:12px; }
        .summary > .fa { position:absolute; top:17px; right:17px; color:var(--brand); opacity:.75; }
        .analytics-grid { display:grid; grid-template-columns:minmax(0,1.6fr) minmax(280px,.7fr); gap:16px; margin-bottom:16px; }
        .section-head { padding:14px 16px; border-bottom:1px solid var(--border); }
        .section-head h2 { margin:0; font-size:14px; } .section-head p { margin:2px 0 0; color:var(--muted); font-size:12px; }
        .chart-box { position:relative; min-height:315px; padding:16px; } .chart-box canvas { max-height:275px; }
        .rank-list { list-style:none; margin:0; padding:6px 16px 12px; }
        .rank-list li { padding:11px 0; border-bottom:1px solid var(--border); } .rank-list li:last-child { border:0; }
        .rank-top { display:flex; justify-content:space-between; gap:10px; font-size:12.5px; }
        .date-link { color:var(--text); font-family:var(--mono); font-weight:700; }
        .severity-track { display:flex; height:6px; margin-top:8px; overflow:hidden; border-radius:9px; background:var(--surface-2); }
        .table-scroll { overflow:auto; max-height:70vh; max-width:100%; }
        table.grid th { position:sticky; top:0; z-index:3; background:var(--surface-2); border-bottom-color:color-mix(in srgb,var(--border) 65%,var(--muted)); }
        table.grid tbody tr:nth-child(even) td { background:color-mix(in srgb,var(--surface-2) 42%,transparent); }
        .count-chip { min-width:38px; justify-content:center; }
        .date-cell { min-width:170px; } .date-cell small { display:block; margin-top:2px; color:var(--muted); }
        .volume-bar { min-width:110px; } .volume-bar .severity-track { margin:0; height:7px; }
        .toolbar-primary { position:sticky; top:56px; z-index:35; background:color-mix(in srgb,var(--surface) 94%,transparent); backdrop-filter:blur(12px); }
        .logline { position:relative; grid-template-columns:76px 112px minmax(0,1fr) auto; padding:9px 16px 9px 19px; }
        .logline:before { content:""; position:absolute; inset:0 auto 0 0; width:3px; background:var(--c,var(--border)); opacity:.85; }
        .logline.open .panel { display:block; animation:panel-in .16s ease-out; }
        @keyframes panel-in { from { opacity:0; transform:translateY(-3px) } to { opacity:1; transform:none } }
        .panel pre { background:var(--code); }
        .insight-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:16px; }
        .insight { display:flex; gap:12px; padding:15px 16px; align-items:flex-start; }
        .insight-icon { flex:0 0 34px; width:34px; height:34px; border-radius:8px; display:grid; place-items:center;
            color:var(--brand); background:color-mix(in srgb,var(--brand) 12%,transparent); }
        .insight strong { display:block; font-size:13px; margin-bottom:2px; }
        .insight p { color:var(--muted); font-size:12px; margin:0; }
        .insight a { display:inline-block; margin-top:6px; font-size:12px; font-weight:650; }
        .copy-icon { flex:0 0 auto; width:28px; height:28px; padding:0; justify-content:center; }
        .path-line { display:flex; align-items:center; gap:7px; min-width:0; }
        .path-line .page-sub { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0; }
        .entry-actions { display:flex; align-items:center; gap:7px; }
        .entry-actions .env { max-width:100px; overflow:hidden; text-overflow:ellipsis; }
        .copy-all-bar { display:flex; align-items:center; gap:8px; }
        .filter-result { display:none; color:var(--muted); font-size:12px; white-space:nowrap; }
        .toast { position:fixed; right:20px; bottom:20px; z-index:200; display:flex; align-items:center; gap:9px;
            padding:10px 13px; color:var(--text); background:var(--surface); border:1px solid var(--border); border-radius:8px;
            box-shadow:0 12px 38px rgba(0,0,0,.22); opacity:0; transform:translateY(8px); pointer-events:none; transition:.18s; }
        .toast.show { opacity:1; transform:none; } .toast .fa { color:var(--brand); }
        .health-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 9px; border-radius:999px; font-size:11px; font-weight:700; }
        .health-ok { color:#087f5b; background:rgba(8,127,91,.12); } .health-watch { color:var(--lvl-warning); background:color-mix(in srgb,var(--lvl-warning) 13%,transparent); }
        .health-risk { color:var(--lvl-error); background:color-mix(in srgb,var(--lvl-error) 13%,transparent); }
        .severity-profile { padding:16px; }
        .severity-overview { display:grid; grid-template-columns:150px minmax(180px,1fr) auto; align-items:center; gap:18px; padding:0 0 16px; border-bottom:1px solid var(--border); }
        .severity-overview strong { display:block; font-size:25px; line-height:1.15; letter-spacing:-.45px; font-variant-numeric:tabular-nums; }
        .severity-eyebrow { display:block; margin-bottom:3px; color:var(--muted); font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.55px; }
        .severity-composition { display:flex; height:13px; overflow:hidden; border-radius:20px; background:var(--surface-2); box-shadow:inset 0 0 0 1px color-mix(in srgb,var(--border) 70%,transparent); }
        .severity-composition span { min-width:2px; transition:filter .12s; } .severity-composition span:hover { filter:brightness(1.15); }
        .severity-overview-meta { display:flex; gap:14px; color:var(--muted); font-size:11px; white-space:nowrap; }
        .severity-columns { display:grid; grid-template-columns:1fr 1fr; gap:28px; padding-top:14px; }
        .severity-group h3 { margin:0 0 4px; color:var(--muted); font-size:10.5px; text-transform:uppercase; letter-spacing:.55px; }
        .severity-row { display:grid; grid-template-columns:105px minmax(70px,1fr) 88px; align-items:center; gap:11px; padding:7px 0; }
        .severity-row + .severity-row { border-top:1px solid var(--border); }
        .severity-name { display:flex; align-items:center; gap:7px; color:var(--c); font-size:12px; font-weight:650; }
        .severity-meter { height:7px; overflow:hidden; border-radius:20px; background:var(--surface-2); }
        .severity-meter span { display:block; height:100%; min-width:2px; border-radius:inherit; background:var(--c); }
        .severity-value { display:flex; justify-content:flex-end; gap:7px; text-align:right; font-size:11px; font-variant-numeric:tabular-nums; }
        .severity-value b { color:var(--text); } .severity-value span { min-width:36px; color:var(--muted); }
        .file-grid { display:flex; flex-direction:column; }
        .file-card { display:grid; grid-template-columns:175px minmax(260px,1fr) auto; align-items:center; gap:22px; padding:15px 16px; background:var(--surface); transition:.12s; }
        .file-card + .file-card { border-top:1px solid var(--border); }
        .file-card:hover { background:color-mix(in srgb,var(--surface-2) 48%,transparent); }
        .file-date { min-width:0; }
        .file-date .date-link { display:block; font-size:13px; }
        .file-date > span { display:block; margin-top:3px; color:var(--muted); font-size:11px; font-variant-numeric:tabular-nums; }
        .file-signal { min-width:0; }
        .file-signal-head { display:flex; justify-content:space-between; gap:12px; margin-bottom:6px; color:var(--muted); font-size:10.5px; }
        .file-signal-head b { font-variant-numeric:tabular-nums; }
        .file-signal .severity-track { height:8px; margin:0; }
        .signal-levels { display:flex; align-items:center; gap:13px; margin-top:7px; min-width:0; }
        .signal-levels a { color:var(--c); font-size:10.5px; white-space:nowrap; }
        .signal-levels a:hover { text-decoration:none; opacity:.8; }
        .signal-levels b { margin-left:3px; font-variant-numeric:tabular-nums; }
        .more-levels { color:var(--muted); font-size:10.5px; white-space:nowrap; }
        .file-card-actions { display:flex; align-items:center; gap:5px; }
        .file-delete { color:var(--muted); } .file-delete:hover { color:var(--lvl-error); border-color:color-mix(in srgb,var(--lvl-error) 45%,var(--border)); }
        .pattern-panel { margin-bottom:16px; }
        .pattern-groups { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:0; }
        .pattern-group { min-width:0; padding:14px 16px; border-right:1px solid var(--border); }
        .pattern-group:last-child { border-right:0; }
        .pattern-group h3 { display:flex; align-items:center; gap:7px; margin:0 0 9px; color:var(--muted); font-size:11px; text-transform:uppercase; letter-spacing:.55px; }
        .pattern-list { display:flex; flex-direction:column; gap:5px; }
        .pattern-chip { width:100%; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:6px 8px; border:1px solid transparent;
            border-radius:6px; color:var(--text); background:var(--surface-2); font:11px var(--mono); cursor:pointer; text-align:left; }
        .pattern-chip:hover,.pattern-chip.active { border-color:var(--brand); color:var(--brand); }
        .pattern-chip .pattern-label { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .pattern-chip .n { color:var(--muted); font-family:var(--sans); }
        .pattern-empty { color:var(--muted); font-size:12px; }
        .active-filter { display:none; align-items:center; gap:7px; padding:7px 10px; border-radius:7px; color:var(--brand); background:color-mix(in srgb,var(--brand) 10%,transparent); font-size:12px; }
        .active-filter.show { display:inline-flex; }
        .pattern-extra { display:none; }
        .pattern-groups.expanded .pattern-extra,.pattern-group.search-match { display:block; }
        .pattern-tools { background:color-mix(in srgb,var(--surface-2) 55%,transparent); }
        .navigation-loader { position:fixed; z-index:300; top:0; left:0; width:100%; height:3px; opacity:0; pointer-events:none; transition:opacity .12s; overflow:hidden; background:color-mix(in srgb,var(--brand) 12%,transparent); }
        .navigation-loader:after { content:""; display:block; width:36%; height:100%; background:var(--brand); box-shadow:0 0 10px color-mix(in srgb,var(--brand) 65%,transparent); transform:translateX(-110%); }
        html.is-navigating .navigation-loader { opacity:1; }
        html.is-navigating .navigation-loader:after { animation:navigation-progress 1s ease-in-out infinite; }
        html.is-navigating [aria-busy="true"] { cursor:progress; }
        @keyframes navigation-progress { 0%{transform:translateX(-110%)} 60%{transform:translateX(190%)} 100%{transform:translateX(290%)} }
        @media (prefers-reduced-motion:reduce) { .navigation-loader:after { animation-duration:2.2s!important; } }
        @media(max-width:900px) { .summary-grid{grid-template-columns:repeat(2,1fr)} .analytics-grid{grid-template-columns:1fr} }
        @media(max-width:640px) {
            .wrap{padding:18px 12px 44px}.topbar{padding:0 12px;gap:10px}.summary-grid{gap:8px}.summary{padding:14px}.summary .value{font-size:22px}
            .logline{grid-template-columns:60px minmax(0,1fr);gap:8px;padding:10px 10px 10px 13px}.logline .lvlcol{grid-column:2;grid-row:1}
            .logline>:nth-child(3){grid-column:1/-1}.logline .acts{grid-column:2;opacity:1}.toolbar-primary{top:56px}
            .insight-grid{grid-template-columns:1fr}.entry-actions .env{display:none}
            .pattern-groups{grid-template-columns:1fr 1fr}.pattern-group{border-bottom:1px solid var(--border)}
            .severity-overview{grid-template-columns:1fr;gap:10px}.severity-overview-meta{justify-content:space-between}.severity-columns{grid-template-columns:1fr;gap:18px}
            .severity-row{grid-template-columns:95px minmax(60px,1fr) 78px}
        }
        @media(max-width:800px) {
            .file-card { grid-template-columns:1fr auto; gap:12px; }
            .file-signal { grid-column:1/-1; grid-row:2; }
            .file-card-actions { grid-column:2; grid-row:1; }
        }
        @media(max-width:520px) {
            .file-card { padding:13px 12px; }
            .file-card-actions .btn-primary { width:30px; overflow:hidden; gap:20px; padding:6px 8px; }
            .signal-levels { gap:8px; flex-wrap:wrap; }
        }

    </style>
</head>
<body>
    <div class="navigation-loader" id="navigation-loader" role="progressbar" aria-label="@lang('Loading')" aria-hidden="true"></div>
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
    <div class="toast" id="copy-toast" role="status" aria-live="polite"><i class="fa fa-check-circle"></i><span>@lang('Copied to clipboard')</span></div>

    <script>
        function ready(fn){ document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn); }
        function beginNavigation(source) {
            document.documentElement.classList.add('is-navigating');
            document.getElementById('navigation-loader').setAttribute('aria-hidden', 'false');
            if (source) source.setAttribute('aria-busy', 'true');
        }
        function copyText(value, button) {
            var done = function () {
                if (button) {
                    var icon = button.querySelector('i'), old = icon ? icon.className : '';
                    if (icon) icon.className = 'fa fa-check';
                    setTimeout(function () { if (icon) icon.className = old; }, 1200);
                }
                var toast = document.getElementById('copy-toast');
                if (toast) { toast.classList.add('show'); clearTimeout(window.lvToastTimer); window.lvToastTimer = setTimeout(function(){ toast.classList.remove('show'); }, 1600); }
            };
            if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(value).then(done);
            var area = document.createElement('textarea'); area.value = value; area.style.position = 'fixed'; area.style.opacity = '0';
            document.body.appendChild(area); area.select(); document.execCommand('copy'); area.remove(); done(); return Promise.resolve();
        }
        ready(function () {
            document.querySelectorAll('[data-copy-text]').forEach(function (button) {
                button.addEventListener('click', function () { copyText(button.dataset.copyText, button); });
            });
            document.querySelectorAll('form[method="GET"], form[method="get"]').forEach(function (form) {
                form.addEventListener('submit', function () { beginNavigation(form); });
            });
            document.addEventListener('click', function (event) {
                var link = event.target.closest('a[href]');
                if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                if (link.target || link.hasAttribute('download') || link.getAttribute('href').charAt(0) === '#') return;
                try {
                    var target = new URL(link.href, location.href);
                    if (target.origin === location.origin && target.href !== location.href) beginNavigation(link);
                } catch (e) {}
            });
            window.addEventListener('pageshow', function () {
                document.documentElement.classList.remove('is-navigating');
                document.querySelectorAll('[aria-busy="true"]').forEach(function (el) { el.removeAttribute('aria-busy'); });
                document.getElementById('navigation-loader').setAttribute('aria-hidden', 'true');
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
