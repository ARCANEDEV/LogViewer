@extends('log-viewer::console._master')
@section('title', 'LogViewer — '.$log->date)

<?php
/** @var  Arcanedev\LogViewer\Entities\Log $log */
/** @var  Illuminate\Pagination\LengthAwarePaginator $entries */
$entries->appends(compact('query'));
$dates = log_viewer()->dates();

/*
 |------------------------------------------------------------------------------
 | Content-derived dimensions (safe, bounded sampling)
 |------------------------------------------------------------------------------
 | Discovers filterable "dimensions" from the entries on THIS page — endpoints,
 | processes, channels, models, source classes, recurring messages, plus any
 | recurring context keys. Sampling is hard-capped by entry count AND bytes so a
 | page of multi-MB lines can never turn this into a CPU / memory sink on prod.
 | Each discovered value becomes a whole-file server search (not a page filter),
 | so it stays correct across every result page of a large log.
 */
$patternBuckets = ['endpoint' => [], 'process' => [], 'channel' => [], 'model' => [], 'source' => [], 'message' => []];
$dynamicDimensions = [];
$analysisEntryLimit = 75;
$analysisByteLimit  = 524288; // 512 KB inspected per page, max
$analysisEntries = 0;
$analysisBytes   = 0;
$ignoredDimensionKeys = ['message', 'exception', 'trace', 'stack', 'context', 'file', 'line', 'timestamp', 'datetime', 'level'];

$addDimension = function (string $key, $value) use (&$dynamicDimensions, $ignoredDimensionKeys): void {
    if (preg_match('~\b(process|channel|model(?:\s+id)?)\s*$~i', trim($key), $knownLabel)) $key = $knownLabel[1];
    $key = strtolower(trim(preg_replace('~[^A-Za-z0-9]+~', '_', $key), '_'));
    $value = is_scalar($value) ? trim((string) $value) : '';
    if ($key === '' || in_array($key, $ignoredDimensionKeys, true) || $value === '' || mb_strlen($value) > 120) return;
    if (preg_match('~^(?:null|true|false|\[\]|\{\})$~i', $value)) return;
    if (! isset($dynamicDimensions[$key]) && count($dynamicDimensions) >= 32) return;
    if (! isset($dynamicDimensions[$key][$value]) && count($dynamicDimensions[$key] ?? []) >= 40) return;
    $dynamicDimensions[$key][$value] = ($dynamicDimensions[$key][$value] ?? 0) + 1;
};
$walkContext = function ($value, string $parent = '', int $depth = 0) use (&$walkContext, $addDimension): void {
    if ($depth > 4 || ! is_array($value)) return;
    foreach ($value as $key => $child) {
        $dimension = is_string($key) ? $key : $parent;
        if (is_array($child)) $walkContext($child, $dimension, $depth + 1);
        elseif ($dimension !== '') $addDimension($dimension, $child);
    }
};

foreach ($entries as $patternEntry) {
    $header      = substr((string) $patternEntry->header, 0, 16384);
    $environment = trim((string) $patternEntry->env);
    if ($analysisEntries >= $analysisEntryLimit || $analysisBytes >= $analysisByteLimit) continue;

    $remainingBytes = $analysisByteLimit - $analysisBytes;
    $contextText = $patternEntry->hasContext() ? substr(str_replace('\/', '/', $patternEntry->context(0)), 0, min(32768, $remainingBytes)) : '';
    $remainingBytes -= strlen($contextText);
    $stackText = $patternEntry->hasStack() && $remainingBytes > 0
        ? substr(html_entity_decode($patternEntry->stack(), ENT_QUOTES | ENT_HTML5), 0, min(8192, $remainingBytes))
        : '';
    $patternText = $header."\n".$contextText."\n".$stackText;
    $analysisEntries++;
    $analysisBytes += strlen($patternText);

    $decodedContext = $contextText !== '' ? json_decode($contextText, true) : null;
    if (is_array($decodedContext)) $walkContext($decodedContext);
    if ($environment !== '') $patternBuckets['channel'][$environment] = ($patternBuckets['channel'][$environment] ?? 0) + 1;

    preg_match_all('~https?://[^\s"\'\)\],]+|/(?:api|auth|v\d+)(?:/[A-Za-z0-9_.?={}&%-]+)+~i', $patternText, $endpointMatches);
    foreach (array_unique($endpointMatches[0] ?? []) as $match) {
        $cleanMatch = rtrim($match, '\\');
        $patternBuckets['endpoint'][$cleanMatch] = ($patternBuckets['endpoint'][$cleanMatch] ?? 0) + 1;
        $path = parse_url($cleanMatch, PHP_URL_PATH);
        if (is_string($path) && $path !== '/' && $path !== $cleanMatch) $patternBuckets['endpoint'][$path] = ($patternBuckets['endpoint'][$path] ?? 0) + 1;
    }
    $routeText = preg_replace('~https?://\S+~i', '', $header);
    preg_match_all('~(?<![A-Za-z0-9_])(?:[A-Za-z0-9_-]+/)+(?:[A-Za-z0-9_-]+)(?![A-Za-z0-9_])~', $routeText, $routeMatches);
    foreach (array_unique($routeMatches[0] ?? []) as $match) $patternBuckets['endpoint'][$match] = ($patternBuckets['endpoint'][$match] ?? 0) + 1;

    preg_match_all('~([A-Za-z][A-Za-z0-9 _.-]{1,32})\s*:=+>\s*(.+?)(?=\s+[A-Za-z][A-Za-z0-9 _.-]{1,32}\s*:=+>|$)~', $header, $labelMatches, PREG_SET_ORDER);
    foreach ($labelMatches as $labelMatch) $addDimension($labelMatch[1], trim($labelMatch[2]));
    preg_match_all('~"(?:process|process_name)"\s*:\s*"([^"]+)"~i', $contextText, $jsonProcess);
    foreach (array_unique($jsonProcess[1] ?? []) as $match) $patternBuckets['process'][$match] = ($patternBuckets['process'][$match] ?? 0) + 1;
    preg_match_all('~"(?:channel|channel_name)"\s*:\s*"([^"]+)"~i', $contextText, $jsonChannel);
    foreach (array_unique($jsonChannel[1] ?? []) as $match) $patternBuckets['channel'][$match] = ($patternBuckets['channel'][$match] ?? 0) + 1;
    preg_match_all('~"(?:model_id|modelId|model)"\s*:\s*"?([^",}\s]+)~i', $contextText, $jsonModel);
    foreach (array_unique($jsonModel[1] ?? []) as $match) $patternBuckets['model'][$match] = ($patternBuckets['model'][$match] ?? 0) + 1;

    preg_match_all('~\b(?:[A-Z][A-Za-z0-9_]*\\\\)+(?:[A-Z][A-Za-z0-9_]*)\b|\b[A-Z][A-Za-z0-9_]*(?:Service|Controller|Job|Handler|Repository|Command|Exception|Error)\b~', $patternText, $sourceMatches);
    foreach (array_unique($sourceMatches[0] ?? []) as $match) $patternBuckets['source'][$match] = ($patternBuckets['source'][$match] ?? 0) + 1;

    $signature = preg_replace(['~https?://\S+~', '~[0-9a-f]{8}-[0-9a-f-]{27,}~i', '~\b\d+\b~', '~\s+~'], ['{url}', '{id}', '{n}', ' '], $header);
    $signature = trim(mb_substr((string) $signature, 0, 72));
    if (mb_strlen($signature) >= 12) $patternBuckets['message'][$signature] = ($patternBuckets['message'][$signature] ?? 0) + 1;
}
foreach ($patternBuckets as $bucket => $patterns) { arsort($patterns); $patternBuckets[$bucket] = array_slice($patterns, 0, 6, true); }
$knownDynamicKeys = ['process', 'process_name', 'channel', 'channel_name', 'model', 'model_id', 'modelid', 'url', 'uri', 'route', 'path'];
foreach ($knownDynamicKeys as $knownKey) unset($dynamicDimensions[$knownKey]);
uasort($dynamicDimensions, fn (array $a, array $b): int => array_sum($b) <=> array_sum($a));
$dynamicDimensions = array_slice($dynamicDimensions, 0, 8, true);
foreach ($dynamicDimensions as $key => $values) { arsort($values); $dynamicDimensions[$key] = array_slice($values, 0, 6, true); }

$patternGroups = [
    'endpoint' => ['icon' => 'link',     'label' => __('endpoints'),   'values' => $patternBuckets['endpoint']],
    'source'   => ['icon' => 'code',     'label' => __('classes'),     'values' => $patternBuckets['source']],
    'process'  => ['icon' => 'cogs',     'label' => __('processes'),   'values' => $patternBuckets['process']],
    'channel'  => ['icon' => 'random',   'label' => __('channels'),    'values' => $patternBuckets['channel']],
    'model'    => ['icon' => 'database', 'label' => __('models/ids'),  'values' => $patternBuckets['model']],
    'message'  => ['icon' => 'clone',    'label' => __('recurring'),   'values' => $patternBuckets['message']],
];
foreach ($dynamicDimensions as $key => $values) {
    $patternGroups['dynamic_'.$key] = ['icon' => 'tag', 'label' => strtolower(str_replace('_', ' ', $key)), 'values' => $values];
}
$patternGroups = array_filter($patternGroups, fn (array $group): bool => ! empty($group['values']));

// Recurring-message counts drive the page-level "Group similar" badges.
$signatureOf = function ($header) {
    $s = preg_replace(['~https?://\S+~', '~[0-9a-f]{8}-[0-9a-f-]{27,}~i', '~\b\d+\b~', '~\s+~'], ['{url}', '{id}', '{n}', ' '], mb_substr((string) $header, 0, 240));
    return trim(mb_substr((string) $s, 0, 140));
};
$sigCounts = [];
foreach ($entries as $e) { $sig = $signatureOf($e->header); $sigCounts[$sig] = ($sigCounts[$sig] ?? 0) + 1; }
$seenSig = [];
?>

@section('content')
    <div style="display:flex; align-items:baseline; gap:12px; flex-wrap:wrap;">
        <h1 class="page-title">@lang('Log') <span class="mono">{{ $log->date }}</span></h1>
        @if ($log->isStreaming())
            <span class="streaming-badge" title="Served by the streaming index engine"><i class="fa fa-bolt"></i> streaming</span>
        @endif
    </div>
    <p class="page-sub mono">{{ $log->getPath() }}</p>

    <div class="lv-layout">
        {{-- Filter rail --}}
        <aside class="lv-side">
            <div class="term" id="dimensions">
                <div class="term-head">
                    <span class="term-dots"><i></i><i></i><i></i></span>
                    <span class="term-title"><b>filters</b></span>
                </div>

                {{-- Severity (server-side links, whole-file accurate) --}}
                <div class="side-sub">@lang('severity')</div>
                <div class="side-levels">
                    @foreach($log->menu() as $levelKey => $item)
                        @php $url = $levelKey === 'all'
                                ? route('log-viewer::logs.show', [$log->date])
                                : route('log-viewer::logs.filter', [$log->date, $levelKey]); @endphp
                        <a class="lvl-{{ $levelKey }} {{ $level === $levelKey ? 'active' : '' }} {{ $item['count'] === 0 ? 'muted' : '' }}" href="{{ $url }}">
                            <span class="dot"></span>
                            <span class="nm">{{ $item['name'] }}</span>
                            <span class="n">{{ number_format($item['count']) }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Dimensions: content-derived, whole-file search chips --}}
                <div class="side-sub">@lang('dimensions') · <span class="mono">{{ $analysisEntries }}e / {{ number_format($analysisBytes / 1024, 0) }}kb @lang('scanned')</span></div>
                @if(count($patternGroups))
                    <div class="term-tools">
                        <span class="term-prompt"><span class="caret">$</span> <span>grep</span>
                            <input id="dim-search" type="search" placeholder="@lang('filter…')" autocomplete="off" aria-label="@lang('Filter dimensions')">
                        </span>
                        @if(count($patternGroups) > 6)
                            <button class="btn btn-sm" id="dim-toggle" data-expanded="false"><i class="fa fa-angle-down"></i> @lang('all')</button>
                        @endif
                    </div>
                    <div class="term-body" id="dim-body">
                        @foreach($patternGroups as $bucket => $group)
                            <div class="dim-row" data-dim-row data-dim-index="{{ $loop->iteration }}"
                                 data-dim-search="{{ e(strtolower($group['label'].' '.implode(' ', array_keys($group['values'])))) }}"
                                 @if($loop->iteration > 6) hidden data-dim-extra @endif>
                                <span class="dim-key"><i class="fa fa-{{ $group['icon'] }}"></i>{{ $group['label'] }}</span>
                                <span class="dim-vals">
                                    @foreach($group['values'] as $pattern => $count)
                                        @php
                                            $filterTerm = $bucket === 'message' ? trim(explode('{', $pattern, 2)[0]) : $pattern;
                                            if (mb_strlen($filterTerm) < 5) $filterTerm = trim(str_replace(['{url}', '{id}', '{n}'], '', $pattern));
                                        @endphp
                                        <button type="button" class="token {{ (string) $query === (string) $filterTerm ? 'active' : '' }}"
                                                data-server-pattern="{{ e($filterTerm) }}"
                                                title="@lang('Search the whole file for:') {{ e($pattern) }}">
                                            <span class="lbl">{{ $pattern }}</span><span class="n">×{{ $count }}</span>
                                        </button>
                                    @endforeach
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="term-empty"># @lang('no reliable dimensions on this page — server search still covers the whole file')</div>
                @endif
            </div>

            <div class="card side-meta">
                <span>@lang('Entries') <b class="num">{{ number_format($entries->total()) }}</b></span>
                <span>@lang('Size') <b>{{ $log->size() }}</b></span>
                <span>@lang('Created') <b>{{ $log->createdAt() }}</b></span>
                <span>@lang('Updated') <b>{{ $log->updatedAt() }}</b></span>
            </div>
        </aside>

        {{-- Main column: search + log stream --}}
        <div class="lv-main">
            <div class="card">
                <div class="toolbar">
                    <select class="input" id="date-switcher" title="Jump to another log file">
                        @foreach($dates as $d)
                            <option value="{{ route('log-viewer::logs.show', [$d]) }}" {{ $d === $log->date ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>

                    <form class="search-wrap" id="server-search-form" action="{{ route('log-viewer::logs.search', [$log->date, $level]) }}" method="GET" style="display:flex; gap:8px;">
                        <span style="position:relative; flex:1;">
                            <i class="fa fa-search"></i>
                            <input class="input" type="search" name="query" value="{{ $query }}" placeholder="@lang('Search this whole log file')…" style="width:100%; padding-left:32px;">
                        </span>
                        <button class="btn btn-primary btn-sm" type="submit">@lang('Search')</button>
                        @unless(is_null($query))
                            <a class="btn btn-sm" href="{{ route('log-viewer::logs.show', [$log->date]) }}"><i class="fa fa-times"></i> {{ $entries->total() }} @lang('results')</a>
                        @endunless
                    </form>

                    <span class="search-wrap" style="max-width:220px;">
                        <i class="fa fa-filter"></i>
                        <input class="input" type="search" id="quick-filter" placeholder="@lang('Filter this page')…" autocomplete="off">
                    </span>

                    <a class="btn btn-sm" href="{{ route('log-viewer::logs.download', [$log->date]) }}"><i class="fa fa-download"></i> @lang('Download')</a>
                    @if (config('log-viewer.delete', false))
                        <button class="btn btn-sm btn-danger" id="delete-btn"><i class="fa fa-trash-o"></i> @lang('Delete')</button>
                    @endif
                </div>
            </div>

    {{-- Log console --}}
    <div class="card" style="margin-top:16px;">
        <div class="card-head" style="justify-content:space-between;">
            <span class="card-title">{{ $level === 'all' ? __('All entries') : ucfirst($level) }}</span>
            <span style="display:flex; gap:8px; align-items:center;">
                <span class="filter-count" id="filter-count"></span>
                <button class="btn btn-sm" id="group-toggle" title="@lang('Collapse repeated messages into one row with a recurrence count')"><i class="fa fa-object-group"></i> @lang('Group similar')</button>
                <button class="btn btn-sm" id="expand-all"><i class="fa fa-angle-double-down"></i> @lang('Expand all')</button>
                <button class="btn btn-sm" id="collapse-all"><i class="fa fa-angle-double-up"></i> @lang('Collapse all')</button>
            </span>
        </div>

        <div id="entries">
            @forelse($entries as $key => $entry)
                <?php /** @var Arcanedev\LogViewer\Entities\LogEntry $entry */
                    // Format-aware: JSON-lines entries carry everything in context,
                    // the default multiline format carries a stack and often no context.
                    // Prefer context; fall back to the stack so no format loses its detail.
                    $hasCtx = $entry->hasContext();
                    $hasStk = $entry->hasStack();
                    $expandable = $hasCtx || $hasStk;
                    $sig     = $signatureOf($entry->header);
                    $isFirst = ! isset($seenSig[$sig]);
                    $seenSig[$sig] = true;
                    $sigN    = $sigCounts[$sig];
                    $search  = strtolower($entry->level.' '.$entry->env.' '.mb_substr($entry->header, 0, 400));
                    $ctx = null;
                    if ($entry->hasContext()) {
                        $decoded = json_decode($entry->context(), true);
                        $ctx = (json_last_error() === JSON_ERROR_NONE)
                            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                            : $entry->context();
                    }
                ?>
                <div class="logline {{ $expandable ? 'is-expandable' : '' }}"
                     data-search="{{ e($search) }}"
                     data-sig-first="{{ $isFirst ? '1' : '0' }}"
                     data-sig-count="{{ $sigN }}">
                    <div class="time" title="{{ $entry->datetime->format('Y-m-d H:i:s') }}">{{ $entry->datetime->format('H:i:s') }}</div>
                    <div class="lvlcol">
                        <span class="chip lvl lvl-{{ $entry->level }}">{!! log_styler()->icon($entry->level) !!} {{ ucfirst($entry->level) }}</span>
                    </div>
                    <div class="lvl-{{ $entry->level }}">
                        @if ($expandable)
                            <button class="expand" data-toggle-line aria-expanded="false">
                                <span class="disc">▶</span>
                                <span class="msg lvl-{{ $entry->level }}" style="border:none; padding:0;">{{ $entry->header }}</span>
                                <span class="count-badge" title="@lang('Occurrences of this message on this page')">×{{ number_format($sigN) }}</span>
                            </button>
                        @else
                            <div class="msg lvl-{{ $entry->level }}">{{ $entry->header }}<span class="count-badge">×{{ number_format($sigN) }}</span></div>
                        @endif

                        @if ($expandable)
                            <div class="panel">
                                <div class="panel-bar">
                                    <span class="panel-label">
                                        @if ($hasCtx)<i class="fa fa-code"></i> @lang('Context')@else<i class="fa fa-list-ol"></i> @lang('Stack trace')@endif
                                    </span>
                                    <span class="panel-meta mono">{{ $entry->datetime->format('Y-m-d H:i:s') }} · {{ $entry->env }}</span>
                                    <button type="button" class="btn btn-sm" data-copy="#body-{{ $key }}"><i class="fa fa-clipboard"></i> @lang('Copy')</button>
                                </div>
                                @if ($hasCtx)
                                    <pre id="body-{{ $key }}" class="ctx-json">{{ $ctx }}</pre>
                                @else
                                    <pre id="body-{{ $key }}" class="stack-content">{!! trim($entry->stack()) !!}</pre>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="acts">
                        <span class="env">{{ $entry->env }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state"><i class="fa fa-inbox fa-2x"></i><p>@lang('No log entries found.')</p></div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($entries->lastPage() > 1)
            <div class="pager">
                <span>@lang('Page') {{ $entries->currentPage() }} / {{ $entries->lastPage() }} · {{ number_format($entries->total()) }} @lang('entries')</span>
                <span class="pages">
                    <a class="btn btn-sm {{ $entries->onFirstPage() ? 'disabled' : '' }}"
                       href="{{ $entries->previousPageUrl() ?: '#' }}" @if($entries->onFirstPage()) style="opacity:.4; pointer-events:none;" @endif>
                        <i class="fa fa-angle-left"></i> @lang('Prev')
                    </a>
                    <a class="btn btn-sm {{ ! $entries->hasMorePages() ? 'disabled' : '' }}"
                       href="{{ $entries->nextPageUrl() ?: '#' }}" @if(! $entries->hasMorePages()) style="opacity:.4; pointer-events:none;" @endif>
                        @lang('Next') <i class="fa fa-angle-right"></i>
                    </a>
                </span>
            </div>
        @endif
    </div>
        </div>{{-- /.lv-main --}}
    </div>{{-- /.lv-layout --}}
@endsection

@section('modals')
    @if (config('log-viewer.delete', false))
        <div class="modal-overlay" id="delete-modal">
            <div class="modal">
                <h3>@lang('Delete log file')</h3>
                <div class="body">@lang('Are you sure you want to delete this log file: :date ?', ['date' => $log->date])</div>
                <div class="foot">
                    <button class="btn" id="delete-cancel">@lang('Cancel')</button>
                    <button class="btn btn-danger" id="delete-confirm">@lang('Delete')</button>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
ready(function () {
    // File switcher
    document.getElementById('date-switcher').addEventListener('change', function (e) { window.lvStartLoading(); location.href = e.target.value; });

    // Show the loader for any control that triggers a server-side navigation.
    document.querySelectorAll('.side-levels a[href], .pager a[href]').forEach(function (a) {
        a.addEventListener('click', function () {
            if (a.getAttribute('href') !== '#' && a.style.pointerEvents !== 'none') window.lvStartLoading(a);
        });
    });
    var serverForm = document.getElementById('server-search-form');
    if (serverForm) {
        serverForm.addEventListener('submit', function () { window.lvStartLoading(serverForm.querySelector('button[type=submit]')); });

        // Clearing the whole-file search auto-refetches the unfiltered page.
        var hasQuery = @json(! is_null($query));
        var showUrl  = "{{ route('log-viewer::logs.show', [$log->date]) }}";
        var qInput   = serverForm.querySelector('[name="query"]');
        if (qInput) qInput.addEventListener('search', function () {
            if (qInput.value.trim() === '' && hasQuery) { window.lvStartLoading(); location.href = showUrl; }
        });
        // The "✕ N results" clear control shows the loader while it refetches.
        var clearLink = serverForm.querySelector('a[href]');
        if (clearLink) clearLink.addEventListener('click', function () { window.lvStartLoading(clearLink); });
    }

    // Expand / collapse a line
    document.querySelectorAll('[data-toggle-line]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var line = btn.closest('.logline');
            var open = line.classList.toggle('open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });
    document.getElementById('expand-all').addEventListener('click', function () {
        document.querySelectorAll('.logline.is-expandable').forEach(function (l) { l.classList.add('open'); });
    });
    document.getElementById('collapse-all').addEventListener('click', function () {
        document.querySelectorAll('.logline.open').forEach(function (l) { l.classList.remove('open'); });
    });

    /* -------- Page-level view aids: quick filter + group similar -------- */
    var rows = Array.prototype.slice.call(document.querySelectorAll('#entries .logline'));
    var qf = document.getElementById('quick-filter');
    var countEl = document.getElementById('filter-count');
    var state = { text: '', group: false };

    function applyFilters() {
        var shown = 0;
        rows.forEach(function (row) {
            var visible = !state.text || (row.dataset.search || '').indexOf(state.text) !== -1;
            var grouped = state.group && row.dataset.sigCount > 1;
            row.classList.toggle('grouped', grouped);
            if (visible && state.group && row.dataset.sigFirst !== '1') visible = false;
            row.style.display = visible ? '' : 'none';
            if (visible) shown++;
        });
        if (countEl) countEl.textContent = shown.toLocaleString() + ' / ' + rows.length.toLocaleString() + ' on this page';
    }
    if (qf) qf.addEventListener('input', function () { state.text = qf.value.toLowerCase().trim(); applyFilters(); });
    var groupBtn = document.getElementById('group-toggle');
    if (groupBtn) groupBtn.addEventListener('click', function () {
        state.group = !state.group;
        groupBtn.classList.toggle('active', state.group);
        applyFilters();
    });
    applyFilters();

    /* -------- Dimensions terminal: whole-file search + grep + show-all -------- */
    document.querySelectorAll('[data-server-pattern]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            window.lvStartLoading(btn);
            var form = document.getElementById('server-search-form');
            form.querySelector('[name="query"]').value = btn.dataset.serverPattern;
            if (form.requestSubmit) form.requestSubmit(); else form.submit();
        });
    });
    var dimSearch = document.getElementById('dim-search');
    if (dimSearch) dimSearch.addEventListener('input', function () {
        var term = dimSearch.value.toLowerCase().trim();
        document.querySelectorAll('[data-dim-row]').forEach(function (row) {
            var match = !term || (row.dataset.dimSearch || '').indexOf(term) !== -1;
            // While grepping, ignore the collapsed "extra" state so matches always surface.
            row.hidden = term ? !match : (row.hasAttribute('data-dim-extra') && !document.getElementById('dim-body').classList.contains('expanded'));
        });
    });
    var dimToggle = document.getElementById('dim-toggle');
    if (dimToggle) dimToggle.addEventListener('click', function () {
        var body = document.getElementById('dim-body');
        var expanded = dimToggle.dataset.expanded === 'true';
        dimToggle.dataset.expanded = expanded ? 'false' : 'true';
        body.classList.toggle('expanded', !expanded);
        document.querySelectorAll('[data-dim-extra]').forEach(function (row) { row.hidden = expanded; });
        dimToggle.innerHTML = !expanded ? '<i class="fa fa-angle-up"></i> {{ __('show less') }}' : '<i class="fa fa-angle-down"></i> {{ __('show all') }}';
    });

    /* -------- Expand panel: copy context -------- */
    function flashOk(btn) {
        var i = btn.querySelector('i'), old = i ? i.className : null;
        if (i) i.className = 'fa fa-check';
        btn.classList.add('ok');
        setTimeout(function () { if (i) i.className = old; btn.classList.remove('ok'); }, 1200);
    }
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var el = document.querySelector(btn.dataset.copy);
            if (!el) return;
            navigator.clipboard.writeText(el.innerText || el.textContent).then(function () { flashOk(btn); });
        });
    });

    /* -------- Context JSON syntax highlighting (lazy, on first expand) -------- */
    function highlightJson(el) {
        if (el.dataset.hl) return;                 // once
        el.dataset.hl = '1';
        var raw = el.textContent;
        // Bounded: skip very large payloads so a huge context can't stall the tab.
        if (raw.length > 60000 || !/^\s*[\{\[]/.test(raw)) return;
        var esc = raw.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        el.innerHTML = esc.replace(
            /("(?:\\u[a-fA-F0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(?:true|false)\b|\bnull\b|-?\d+(?:\.\d+)?(?:[eE][+\-]?\d+)?)/g,
            function (m) {
                var cls = 'jn';
                if (m[0] === '"') cls = /:\s*$/.test(m) ? 'jk' : 'js';
                else if (m === 'true' || m === 'false') cls = 'jb';
                else if (m === 'null') cls = 'jz';
                return '<span class="' + cls + '">' + m + '</span>';
            }
        );
    }
    // Highlight a row's context the first time it is expanded (keeps initial paint cheap).
    document.querySelectorAll('[data-toggle-line]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var pre = btn.closest('.logline').querySelector('pre.ctx-json');
            if (pre) highlightJson(pre);
        });
    });
    document.getElementById('expand-all').addEventListener('click', function () {
        document.querySelectorAll('pre.ctx-json').forEach(highlightJson);
    });

    // Highlight configured tokens in stack traces
    @unless (empty(log_styler()->toHighlight()))
        var re = new RegExp('({!! join('|', log_styler()->toHighlight()) !!})', 'gm');
        document.querySelectorAll('.stack-content').forEach(function (el) {
            el.innerHTML = el.innerHTML.replace(re, '<strong>$1</strong>');
        });
    @endunless

    // Delete modal
@if (config('log-viewer.delete', false))
    var modal = document.getElementById('delete-modal');
    document.getElementById('delete-btn').addEventListener('click', function () { modal.classList.add('show'); });
    document.getElementById('delete-cancel').addEventListener('click', function () { modal.classList.remove('show'); });
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('show'); });
    document.getElementById('delete-confirm').addEventListener('click', function () {
        fetch("{{ route('log-viewer::logs.delete') }}", {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ date: '{{ $log->date }}' })
        }).then(function (r) { return r.json(); }).then(function (r) {
            if (r.result === 'success') location.replace("{{ route('log-viewer::logs.list') }}");
            else alert('Delete failed — check the console.');
        }).catch(function (e) { alert('Delete failed — check the console.'); console.error(e); });
    });
@endif
});
</script>
@endsection
