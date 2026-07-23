@extends('log-viewer::observatory._master')
@section('title', 'LogViewer — '.$log->date)

<?php
/** @var  Arcanedev\LogViewer\Entities\Log $log */
/** @var  Illuminate\Pagination\LengthAwarePaginator $entries */
$entries->appends(compact('query'));
$dates = log_viewer()->dates();
$menu = $log->menu();
$fileTotal = (int) ($menu['all']['count'] ?? 0);
$fileDanger = 0;
foreach (['emergency', 'alert', 'critical', 'error'] as $severity) {
    $fileDanger += (int) ($menu[$severity]['count'] ?? 0);
}
$fileWarning = (int) ($menu['warning']['count'] ?? 0);
$fileDangerRate = $fileTotal > 0 ? ($fileDanger / $fileTotal) * 100 : 0;
$patternBuckets = ['endpoint' => [], 'process' => [], 'channel' => [], 'model' => [], 'source' => [], 'message' => []];
$dynamicDimensions = [];
$analysisEntryLimit = 75;
$analysisByteLimit = 524288;
$analysisEntries = 0;
$analysisBytes = 0;
$pageHasExpandableEntries = false;
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
$hourlyErrors = array_fill(0, 24, 0);
foreach ($entries as $patternEntry) {
    if ($patternEntry->hasStack() || $patternEntry->hasContext()) $pageHasExpandableEntries = true;
    $header = substr((string) $patternEntry->header, 0, 16384);
    $environment = trim((string) $patternEntry->env);
    if (in_array($patternEntry->level, ['emergency', 'alert', 'critical', 'error'], true)) {
        $hourlyErrors[(int) $patternEntry->datetime->format('G')]++;
    }
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

    foreach (['process' => 'Process', 'channel' => 'Channel', 'model' => 'Model(?:\s+Id)?'] as $bucket => $keyPattern) {
        preg_match_all('~'.$keyPattern.'\s*:=+>\s*([A-Za-z0-9_.:@/\\\\-]+)~i', $patternText, $dimensionMatches);
        foreach (array_unique($dimensionMatches[1] ?? []) as $match) {
            $value = trim($match);
            if ($value !== '') $patternBuckets[$bucket][$value] = ($patternBuckets[$bucket][$value] ?? 0) + 1;
        }
    }
    preg_match_all('~([A-Za-z][A-Za-z0-9 _.-]{1,32})\s*:=+>\s*(.+?)(?=\s+[A-Za-z][A-Za-z0-9 _.-]{1,32}\s*:=+>|$)~', $header, $labelMatches, PREG_SET_ORDER);
    foreach ($labelMatches as $labelMatch) $addDimension($labelMatch[1], trim($labelMatch[2]));
    preg_match_all('~"(?:process|process_name)"\s*:\s*"([^"]+)"~i', $contextText, $jsonProcess);
    foreach (array_unique($jsonProcess[1] ?? []) as $match) $patternBuckets['process'][$match] = ($patternBuckets['process'][$match] ?? 0) + 1;
    preg_match_all('~"(?:channel|channel_name)"\s*:\s*"([^"]+)"~i', $contextText, $jsonChannel);
    foreach (array_unique($jsonChannel[1] ?? []) as $match) $patternBuckets['channel'][$match] = ($patternBuckets['channel'][$match] ?? 0) + 1;
    preg_match_all('~"(?:model_id|modelId|model)"\s*:\s*"?([^",}\s]+)~i', $contextText, $jsonModel);
    foreach (array_unique($jsonModel[1] ?? []) as $match) $patternBuckets['model'][$match] = ($patternBuckets['model'][$match] ?? 0) + 1;

    preg_match_all('~\b(?:[A-Z][A-Za-z0-9_]*\\\\)+(?:[A-Z][A-Za-z0-9_]*)\b|\b[A-Z][A-Za-z0-9_]*(?:Service|Controller|Job|Handler|Repository|Command)\b~', $patternText, $sourceMatches);
    foreach (array_unique($sourceMatches[0] ?? []) as $match) $patternBuckets['source'][$match] = ($patternBuckets['source'][$match] ?? 0) + 1;

    $signature = preg_replace(['~https?://\S+~', '~[0-9a-f]{8}-[0-9a-f-]{27,}~i', '~\b\d+\b~', '~\s+~'], ['{url}', '{id}', '{n}', ' '], $header);
    $signature = trim(mb_substr((string) $signature, 0, 72));
    if (mb_strlen($signature) >= 12) $patternBuckets['message'][$signature] = ($patternBuckets['message'][$signature] ?? 0) + 1;
}
foreach ($patternBuckets as $bucket => $patterns) {
    arsort($patterns);
    $patternBuckets[$bucket] = array_slice($patterns, 0, 5, true);
}
$knownDynamicKeys = ['process', 'process_name', 'channel', 'channel_name', 'model', 'model_id', 'modelid', 'url', 'uri', 'route', 'path'];
foreach ($knownDynamicKeys as $knownKey) unset($dynamicDimensions[$knownKey]);
uasort($dynamicDimensions, function (array $a, array $b): int { return array_sum($b) <=> array_sum($a); });
$dynamicDimensions = array_slice($dynamicDimensions, 0, 8, true);
foreach ($dynamicDimensions as $key => $values) {
    arsort($values);
    $dynamicDimensions[$key] = array_slice($values, 0, 5, true);
}
$patternGroups = [
    'endpoint' => ['icon' => 'link', 'label' => __('URLs & routes'), 'values' => $patternBuckets['endpoint']],
    'process' => ['icon' => 'cogs', 'label' => __('Processes'), 'values' => $patternBuckets['process']],
    'channel' => ['icon' => 'random', 'label' => __('Channels'), 'values' => $patternBuckets['channel']],
    'model' => ['icon' => 'database', 'label' => __('Models / IDs'), 'values' => $patternBuckets['model']],
    'source' => ['icon' => 'code', 'label' => __('Classes & services'), 'values' => $patternBuckets['source']],
    'message' => ['icon' => 'clone', 'label' => __('Recurring messages'), 'values' => $patternBuckets['message']],
];
foreach ($dynamicDimensions as $key => $values) {
    $patternGroups['dynamic_'.$key] = ['icon' => 'tag', 'label' => ucwords(str_replace('_', ' ', $key)), 'values' => $values];
}
$patternGroups = array_filter($patternGroups, fn (array $group): bool => ! empty($group['values']));
?>

@section('content')
    <div style="display:flex; align-items:baseline; gap:12px; flex-wrap:wrap;">
        <h1 class="page-title">@lang('Log') <span class="mono">{{ $log->date }}</span></h1>
        @if ($log->isStreaming())
            <span class="streaming-badge" title="Served by the streaming index engine"><i class="fa fa-bolt"></i> streaming</span>
        @endif
    </div>
    <div class="path-line">
        <p class="page-sub mono" id="log-path">{{ $log->getPath() }}</p>
        <button class="btn btn-sm copy-icon" data-copy-text="{{ e($log->getPath()) }}" title="@lang('Copy file path')" aria-label="@lang('Copy file path')"><i class="fa fa-clipboard"></i></button>
    </div>

    <div class="summary-grid">
        <div class="card summary"><i class="fa fa-list-ul"></i><div class="label">@lang('File events')</div><div class="value">{{ number_format($fileTotal) }}</div><div class="hint">@lang('Whole-file indexed count')</div></div>
        <div class="card summary"><i class="fa fa-exclamation-circle"></i><div class="label">@lang('High severity')</div><div class="value" style="color:var(--lvl-error)">{{ number_format($fileDanger) }}</div><div class="hint">{{ number_format($fileDangerRate, 1) }}% @lang('of this file')</div></div>
        <div class="card summary"><i class="fa fa-warning"></i><div class="label">@lang('Warnings')</div><div class="value" style="color:var(--lvl-warning)">{{ number_format($fileWarning) }}</div><div class="hint">@lang('Potential degradation signals')</div></div>
        <div class="card summary"><i class="fa fa-filter"></i><div class="label">@lang('Current result')</div><div class="value">{{ number_format($entries->total()) }}</div><div class="hint">{{ $level === 'all' ? __('All severity levels') : ucfirst($level) }}{{ $query ? ' · '.__('search applied') : '' }}</div></div>
    </div>

    <div class="card pattern-panel">
        <div class="section-head">
            <div><h2>@lang('Discovered dimensions')</h2><p>@lang('Suggestions are sampled safely from this page; selecting one searches the entire log across every result page.') {{ $analysisEntries }} @lang('entries') · {{ number_format($analysisBytes / 1024, 0) }} KB @lang('inspected').</p></div>
            <span class="chip"><span class="n">{{ count($patternGroups) }}</span> @lang('filter groups')</span>
        </div>
        @if(count($patternGroups))
            <div class="toolbar pattern-tools">
                <span class="search-wrap" style="max-width:300px"><i class="fa fa-search"></i><input class="input" id="pattern-search" type="search" placeholder="@lang('Find a filter group or value')…" autocomplete="off"></span>
                @if(count($patternGroups) > 6)<button class="btn btn-sm" id="toggle-patterns" data-expanded="false"><i class="fa fa-plus"></i> @lang('Show all dimensions')</button>@endif
                <span class="filter-result" id="pattern-result" aria-live="polite"></span>
            </div>
        @endif
        <div class="pattern-groups" id="pattern-groups">
            @forelse($patternGroups as $bucket => $group)
                <div class="pattern-group {{ $loop->iteration > 6 ? 'pattern-extra' : '' }}" data-pattern-group data-pattern-index="{{ $loop->iteration }}" data-pattern-search="{{ e(strtolower($group['label'].' '.implode(' ', array_keys($group['values'])))) }}">
                    <h3><i class="fa fa-{{ $group['icon'] }}"></i> {{ $group['label'] }}</h3>
                    <div class="pattern-list">
                        @foreach($group['values'] as $pattern => $count)
                            @php
                                $filterTerm = $bucket === 'message' ? trim(explode('{', $pattern, 2)[0]) : $pattern;
                                if (mb_strlen($filterTerm) < 5) $filterTerm = trim(str_replace(['{url}', '{id}', '{n}'], '', $pattern));
                            @endphp
                            <button class="pattern-chip {{ (string) $query === (string) $filterTerm ? 'active' : '' }}" data-server-pattern="{{ e($filterTerm) }}" title="@lang('Search the entire log for:') {{ e($pattern) }}">
                                <span class="pattern-label">{{ $pattern }}</span><span class="n">{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="empty-state" style="grid-column:1/-1;padding:24px"><i class="fa fa-search"></i><p>@lang('No reliable dimensions were found on this page. Search still covers message and context text.')</p></div>
            @endforelse
        </div>
    </div>

    <div class="card" style="margin-bottom:16px">
        <div class="section-head"><h2>@lang('High-severity activity by hour')</h2><p>@lang('Emergency through error on the current result page · use server search or severity filters to change the sample')</p></div>
        <div class="chart-box" style="min-height:220px"><canvas id="hourly-error-chart"></canvas></div>
    </div>

    {{-- Toolbar: file switcher · server search · live filter --}}
    <div class="card">
        <div class="toolbar toolbar-primary">
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

            <span class="search-wrap" style="max-width:240px;">
                <i class="fa fa-filter"></i>
                <input class="input" type="search" id="quick-filter" placeholder="@lang('Filter visible page only')…" autocomplete="off">
            </span>
            <span class="active-filter" id="active-pattern"><i class="fa fa-filter"></i><span></span><button class="btn btn-sm copy-icon" id="clear-pattern" aria-label="@lang('Clear pattern filter')"><i class="fa fa-times"></i></button></span>

            <a class="btn btn-sm" href="{{ route('log-viewer::logs.download', [$log->date]) }}"><i class="fa fa-download"></i> @lang('Download')</a>
            <button class="btn btn-sm btn-danger" id="delete-btn"><i class="fa fa-trash-o"></i> @lang('Delete')</button>
        </div>

        {{-- Level chip bar (server-side, whole-file accurate) --}}
        <div class="toolbar" style="gap:8px;">
            @foreach($menu as $levelKey => $item)
                @php $url = $levelKey === 'all'
                        ? route('log-viewer::logs.show', [$log->date])
                        : route('log-viewer::logs.filter', [$log->date, $levelKey]); @endphp
                <a class="chip lvl lvl-{{ $levelKey }} {{ $level === $levelKey ? 'active' : '' }} {{ $item['count'] === 0 ? 'muted' : '' }}"
                   href="{{ $url }}">
                    {!! $item['icon'] !!} {{ $item['name'] }} <span class="n">{{ number_format($item['count']) }}</span>
                </a>
            @endforeach
        </div>

        <div class="meta">
            <span>@lang('Entries') <b class="num">{{ number_format($entries->total()) }}</b></span>
            <span>@lang('Size') <b>{{ $log->size() }}</b></span>
            <span>@lang('Created') <b>{{ $log->createdAt() }}</b></span>
            <span>@lang('Updated') <b>{{ $log->updatedAt() }}</b></span>
        </div>
    </div>

    {{-- Log console --}}
    <div class="card" style="margin-top:16px;">
        <div class="card-head" style="justify-content:space-between;">
            <span class="card-title">{{ $level === 'all' ? __('All entries') : ucfirst($level) }}</span>
            <span style="display:flex; gap:8px;">
                <button class="btn btn-sm" id="copy-visible"><i class="fa fa-clipboard"></i> @lang('Copy visible')</button>
                @if($pageHasExpandableEntries)
                    <button class="btn btn-sm" id="expand-all"><i class="fa fa-angle-double-down"></i> @lang('Expand all')</button>
                    <button class="btn btn-sm" id="collapse-all"><i class="fa fa-angle-double-up"></i> @lang('Collapse all')</button>
                @endif
            </span>
        </div>

        <div id="entries">
            @forelse($entries as $key => $entry)
                <?php /** @var Arcanedev\LogViewer\Entities\LogEntry $entry */
                    $stackContent = $entry->hasStack() ? trim($entry->stack()) : '';
                    $contextContent = $entry->hasContext() ? trim($entry->context()) : '';
                    $hasStackContent = $stackContent !== '';
                    $hasContextContent = ! in_array($contextContent, ['', 'null', '[]', '{}'], true);
                    $expandable = $hasStackContent || $hasContextContent;
                    $contextSearch = $hasContextContent ? substr(str_replace('\/', '/', $contextContent), 0, 8192) : '';
                    $stackSearch = $hasStackContent ? substr(html_entity_decode($stackContent, ENT_QUOTES | ENT_HTML5), 0, 4096) : '';
                    $search = strtolower(substr($entry->level.' '.$entry->env.' '.$entry->header.' '.$contextSearch.' '.$stackSearch, 0, 16384));
                ?>
                <div class="logline lvl-{{ $entry->level }}" data-search="{{ e($search) }}" data-copy-line="{{ e('['.$entry->datetime->format('H:i:s').'] '.strtoupper($entry->level).' ['.$entry->env.'] '.$entry->header) }}">
                    <div class="time">{{ $entry->datetime->format('H:i:s') }}</div>
                    <div class="lvlcol">
                        <span class="chip lvl lvl-{{ $entry->level }}">{!! log_styler()->icon($entry->level) !!} {{ ucfirst($entry->level) }}</span>
                    </div>
                    <div class="lvl-{{ $entry->level }}">
                        @if($expandable)
                            <button class="expand" data-toggle-line>
                                <span class="disc">▶</span>
                                <span class="msg lvl-{{ $entry->level }}" style="border:none;padding:0">{{ $entry->header }}</span>
                            </button>
                            <div class="panel">
                                @if($hasStackContent)
                                    <div class="panel-label">@lang('Stack trace')</div>
                                    <pre class="stack-content">{!! $stackContent !!}</pre>
                                @endif
                                @if($hasContextContent)
                                    <div class="panel-label">@lang('Context')</div>
                                    <pre class="context-content">{{ $contextContent }}</pre>
                                @endif
                            </div>
                        @else
                            <div class="msg lvl-{{ $entry->level }}">{{ $entry->header }}</div>
                        @endif
                    </div>
                    <div class="acts entry-actions">
                        <span class="env">{{ $entry->env }}</span>
                        <button class="btn btn-sm copy-icon" data-copy-entry title="@lang('Copy complete entry')" aria-label="@lang('Copy complete entry')"><i class="fa fa-clipboard"></i></button>
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
@endsection

@section('modals')
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
ready(function () {
    // File switcher
    document.getElementById('date-switcher').addEventListener('change', function (e) { beginNavigation(e.target); location.href = e.target.value; });

    // Expansion is available only for entries that actually contain stack or context data.
    document.querySelectorAll('[data-toggle-line]').forEach(function (button) {
        button.addEventListener('click', function () { button.closest('.logline').classList.toggle('open'); });
    });
    var expandAll = document.getElementById('expand-all'), collapseAll = document.getElementById('collapse-all');
    if (expandAll) expandAll.addEventListener('click', function () {
        document.querySelectorAll('.logline .panel').forEach(function (panel) { panel.closest('.logline').classList.add('open'); });
    });
    if (collapseAll) collapseAll.addEventListener('click', function () {
        document.querySelectorAll('.logline.open').forEach(function (line) { line.classList.remove('open'); });
    });

    // Live client-side filter of the current page
    var qf = document.getElementById('quick-filter');
    qf.addEventListener('input', function () {
        var term = qf.value.toLowerCase().trim();
        var visible = 0;
        document.querySelectorAll('#entries .logline').forEach(function (row) {
            var show = !term || (row.dataset.search || '').indexOf(term) !== -1;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        qf.setAttribute('aria-label', visible + ' visible entries');
    });
    document.querySelectorAll('[data-server-pattern]').forEach(function (button) {
        button.addEventListener('click', function () {
            var form = document.getElementById('server-search-form');
            form.querySelector('[name="query"]').value = button.dataset.serverPattern;
            if (form.requestSubmit) form.requestSubmit(); else form.submit();
        });
    });
    document.getElementById('clear-pattern').addEventListener('click', function () {
        qf.value = ''; qf.dispatchEvent(new Event('input'));
        document.querySelectorAll('[data-server-pattern].active').forEach(function (item) { item.classList.remove('active'); });
        document.getElementById('active-pattern').classList.remove('show');
    });
    var patternSearch = document.getElementById('pattern-search');
    var patternGroups = document.getElementById('pattern-groups');
    var patternToggle = document.getElementById('toggle-patterns');
    if (patternToggle) patternToggle.addEventListener('click', function () {
        var expanded = patternToggle.dataset.expanded === 'true';
        patternToggle.dataset.expanded = expanded ? 'false' : 'true';
        patternGroups.classList.toggle('expanded', !expanded);
        patternToggle.innerHTML = !expanded ? '<i class="fa fa-minus"></i> {{ __('Show fewer dimensions') }}' : '<i class="fa fa-plus"></i> {{ __('Show all dimensions') }}';
    });
    if (patternSearch) patternSearch.addEventListener('input', function () {
        var term = patternSearch.value.toLowerCase().trim(), matches = 0;
        document.querySelectorAll('[data-pattern-group]').forEach(function (group) {
            var match = !term || group.dataset.patternSearch.indexOf(term) !== -1;
            group.classList.toggle('search-match', !!term && match);
            group.style.display = term && !match ? 'none' : '';
            if (match) matches++;
        });
        var result = document.getElementById('pattern-result');
        result.textContent = term ? matches + ' matching groups' : '';
        result.style.display = term ? 'inline' : 'none';
    });

    // Copy buttons
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var el = document.querySelector(btn.dataset.copy);
            if (el) copyText(el.innerText, btn);
        });
    });
    document.getElementById('copy-visible').addEventListener('click', function () {
        var lines = Array.from(document.querySelectorAll('#entries .logline'))
            .filter(function (row) { return row.style.display !== 'none'; })
            .map(function (row) { return row.dataset.copyLine; });
        copyText(lines.join('\n'), this);
    });
    document.querySelectorAll('[data-copy-entry]').forEach(function (button) {
        button.addEventListener('click', function () {
            var row = button.closest('.logline'), text = row.dataset.copyLine;
            var stack = row.querySelector('.stack-content'), context = row.querySelector('.context-content');
            if (stack) text += '\n\nStack trace:\n' + stack.innerText;
            if (context) text += '\n\nContext:\n' + context.innerText;
            copyText(text, button);
        });
    });

    if (typeof Chart !== 'undefined') {
        var chartStyle = getComputedStyle(document.documentElement);
        new Chart(document.getElementById('hourly-error-chart'), {
            type: 'line',
            data: {
                labels: Array.from({length:24}, function(_, hour) { return String(hour).padStart(2, '0') + ':00'; }),
                datasets: [{ label: 'High severity', data: @json(array_values($hourlyErrors)), borderColor: chartStyle.getPropertyValue('--lvl-error').trim(), backgroundColor: 'rgba(239,68,68,.10)', borderWidth: 2.5, pointRadius: 2, tension: .3, fill: true }]
            },
            options: { responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false}, scales:{
                x:{ticks:{color:chartStyle.getPropertyValue('--muted').trim(),maxTicksLimit:8},grid:{display:false}},
                y:{beginAtZero:true,ticks:{color:chartStyle.getPropertyValue('--muted').trim(),precision:0},grid:{color:chartStyle.getPropertyValue('--grid').trim()}}
            }, plugins:{legend:{display:false}} }
        });
    }

    // Highlight configured tokens in stack traces
    @unless (empty(log_styler()->toHighlight()))
        var re = new RegExp('({!! join('|', log_styler()->toHighlight()) !!})', 'gm');
        document.querySelectorAll('.stack-content').forEach(function (el) {
            el.innerHTML = el.innerHTML.replace(re, '<strong>$1</strong>');
        });
    @endunless

    // Delete modal
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
});
</script>
@endsection
