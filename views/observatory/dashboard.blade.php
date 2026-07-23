@extends('log-viewer::observatory._master')
@section('title', 'LogViewer — Observatory')

@php
    $stats = log_viewer()->stats();
    $levels = ['emergency','alert','critical','error','warning','notice','info','debug'];
    $dangerLevels = ['emergency','alert','critical','error'];
    $total = array_sum(array_column($percents, 'count'));
    $dangerTotal = 0;
    foreach ($dangerLevels as $severity) $dangerTotal += $percents[$severity]['count'] ?? 0;
    $errorRate = $total > 0 ? ($dangerTotal / $total) * 100 : 0;
    $maxLevelCount = max(1, ...array_values(array_map(fn ($item) => (int) $item['count'], $percents)));
    $activeFiles = count($stats);
    $latest = $activeFiles ? array_key_last($stats) : null;
    $statDates = array_keys($stats);
    $latestRow = $latest ? ($stats[$latest] ?? []) : [];
    $previousDate = count($statDates) > 1 ? $statDates[count($statDates) - 2] : null;
    $previousRow = $previousDate ? ($stats[$previousDate] ?? []) : [];
    $latestDanger = 0; $previousDanger = 0;
    foreach ($dangerLevels as $severity) {
        $latestDanger += (int) ($latestRow[$severity] ?? 0);
        $previousDanger += (int) ($previousRow[$severity] ?? 0);
    }
    $dangerDelta = $previousDanger > 0 ? (($latestDanger - $previousDanger) / $previousDanger) * 100 : null;
    $busiestDate = null; $busiestTotal = 0;
    foreach ($stats as $date => $row) {
        $rowVolume = (int) ($row['all'] ?? array_sum($row));
        if ($rowVolume > $busiestTotal) { $busiestDate = $date; $busiestTotal = $rowVolume; }
    }
    $healthClass = $errorRate >= 10 ? 'health-risk' : ($errorRate >= 2 ? 'health-watch' : 'health-ok');
    $healthLabel = $errorRate >= 10 ? __('Needs attention') : ($errorRate >= 2 ? __('Monitor') : __('Nominal'));
    $trendRows = array_slice($stats, -14, 14, true);
    $ranked = $stats;
    uasort($ranked, function ($a, $b) use ($dangerLevels) {
        return array_sum(array_intersect_key($b, array_flip($dangerLevels))) <=> array_sum(array_intersect_key($a, array_flip($dangerLevels)));
    });
    $ranked = array_slice($ranked, 0, 6, true);
    $trendLabels = array_keys($trendRows);
    $trendDatasets = [];
    foreach (array_merge($dangerLevels, ['warning']) as $severity) {
        $trendDatasets[] = ['label' => ucfirst($severity), 'level' => $severity, 'data' => array_map(fn ($row) => (int) ($row[$severity] ?? 0), array_values($trendRows))];
    }
@endphp

@section('content')
    <h1 class="page-title">@lang('Observatory')</h1>
    <p class="page-sub">@lang('Operational health, risk signals, and log volume across indexed files.')</p>

    <div class="summary-grid">
        <div class="card summary"><i class="fa fa-database"></i><div class="label">@lang('Total events')</div><div class="value">{{ number_format($total) }}</div><div class="hint">@lang('Across all indexed files')</div></div>
        <div class="card summary"><i class="fa fa-exclamation-triangle"></i><div class="label">@lang('High severity')</div><div class="value" style="color:var(--lvl-error)">{{ number_format($dangerTotal) }}</div><div class="hint">{{ number_format($errorRate, 1) }}% @lang('of all events') · <span class="health-pill {{ $healthClass }}">{{ $healthLabel }}</span></div></div>
        <div class="card summary"><i class="fa fa-files-o"></i><div class="label">@lang('Indexed files')</div><div class="value">{{ number_format($activeFiles) }}</div><div class="hint">@lang('Ready for investigation')</div></div>
        <div class="card summary"><i class="fa fa-clock-o"></i><div class="label">@lang('Latest file')</div><div class="value mono" style="font-size:20px">{{ $latest ?: '—' }}</div><div class="hint">@lang('Most recent indexed date')</div></div>
    </div>

    <div class="insight-grid">
        <div class="card insight">
            <span class="insight-icon"><i class="fa fa-line-chart"></i></span>
            <div><strong>@lang('Latest high-severity movement')</strong>
                <p>
                    @if(is_null($dangerDelta)) @lang('A previous indexed date is needed for comparison.')
                    @elseif($dangerDelta > 0) {{ number_format(abs($dangerDelta), 1) }}% @lang('higher than') {{ $previousDate }}.
                    @elseif($dangerDelta < 0) {{ number_format(abs($dangerDelta), 1) }}% @lang('lower than') {{ $previousDate }}.
                    @else @lang('Unchanged from') {{ $previousDate }}.
                    @endif
                </p>
                @if($latest)<a href="{{ route('log-viewer::logs.filter', [$latest, 'error']) }}">@lang('Inspect latest errors') <i class="fa fa-arrow-right"></i></a>@endif
            </div>
        </div>
        <div class="card insight">
            <span class="insight-icon"><i class="fa fa-area-chart"></i></span>
            <div><strong>@lang('Peak volume')</strong><p>@if($busiestDate)<span class="mono">{{ $busiestDate }}</span> @lang('contains') {{ number_format($busiestTotal) }} @lang('events').@else @lang('No volume data is available yet.')@endif</p>
                @if($busiestDate)<a href="{{ route('log-viewer::logs.show', [$busiestDate]) }}">@lang('Open busiest file') <i class="fa fa-arrow-right"></i></a>@endif
            </div>
        </div>
        <div class="card insight">
            <span class="insight-icon"><i class="fa fa-clipboard"></i></span>
            <div><strong>@lang('Shareable snapshot')</strong><p>@lang('Copy the key operational totals for a ticket or incident update.')</p>
                <button class="btn btn-sm" data-copy-text="{{ e(__('Log snapshot: :total events, :danger high severity (:rate%), :files indexed files, latest :latest.', ['total' => number_format($total), 'danger' => number_format($dangerTotal), 'rate' => number_format($errorRate, 1), 'files' => number_format($activeFiles), 'latest' => $latest ?: 'n/a'])) }}"><i class="fa fa-clipboard"></i> @lang('Copy summary')</button>
            </div>
        </div>
    </div>

    <div class="analytics-grid">
        <div class="card">
            <div class="section-head"><h2>@lang('Error trend over time')</h2><p>@lang('Emergency, alert, critical, error, and warning · last 14 indexed dates')</p></div>
            <div class="chart-box"><canvas id="trend-chart"></canvas></div>
        </div>
        <div class="card">
            <div class="section-head"><h2>@lang('Highest-risk files')</h2><p>@lang('Ranked by emergency through error')</p></div>
            <ol class="rank-list">
                @forelse($ranked as $date => $row)
                    @php
                        $rowTotal = max(1, (int) ($row['all'] ?? array_sum($row)));
                        $rowDanger = 0; foreach ($dangerLevels as $severity) $rowDanger += (int) ($row[$severity] ?? 0);
                    @endphp
                    <li>
                        <div class="rank-top"><a class="date-link" href="{{ route('log-viewer::logs.show', [$date]) }}">{{ $date }}</a><span><b style="color:var(--lvl-error)">{{ number_format($rowDanger) }}</b> / {{ number_format($rowTotal) }}</span></div>
                        <div class="severity-track" title="{{ number_format(($rowDanger / $rowTotal) * 100, 1) }}% high severity">
                            <span style="width:{{ min(100, ($rowDanger / $rowTotal) * 100) }}%;background:var(--lvl-error)"></span>
                        </div>
                    </li>
                @empty
                    <li class="empty-state">@lang('No indexed files yet.')</li>
                @endforelse
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="section-head">
            <div><h2>@lang('Severity profile')</h2><p>@lang('Composition shows share of total; row bars compare relative frequency')</p></div>
            <span class="health-pill {{ $healthClass }}"><i class="fa fa-circle"></i> {{ $healthLabel }}</span>
        </div>
        <div class="severity-profile">
            <div class="severity-overview">
                <div>
                    <span class="severity-eyebrow">@lang('All indexed events')</span>
                    <strong>{{ number_format($total) }}</strong>
                </div>
                <div class="severity-composition" aria-label="@lang('Severity composition')">
                    @foreach($percents as $severity => $item)
                        @if((float) $item['percent'] > 0)
                            <span class="lvl-{{ $severity }}" style="width:{{ $item['percent'] }}%;background:var(--c)" title="{{ $item['name'] }}: {{ number_format($item['count']) }} ({{ number_format((float) $item['percent'], 1) }}%)"></span>
                        @endif
                    @endforeach
                </div>
                <div class="severity-overview-meta"><span><b style="color:var(--lvl-error)">{{ number_format($dangerTotal) }}</b> @lang('high severity')</span><span>{{ number_format($errorRate, 1) }}% @lang('risk share')</span></div>
            </div>

            <div class="severity-columns">
                @foreach([
                    ['label' => __('Action required'), 'icon' => 'exclamation-triangle', 'items' => $dangerLevels],
                    ['label' => __('Operational signal'), 'icon' => 'signal', 'items' => ['warning','notice','info','debug']]
                ] as $group)
                    <section class="severity-group">
                        <h3><i class="fa fa-{{ $group['icon'] }}"></i> {{ $group['label'] }}</h3>
                        @foreach($group['items'] as $severity)
                            @php $item = $percents[$severity]; @endphp
                            <div class="severity-row lvl-{{ $severity }}" style="{{ $item['count'] === 0 ? 'opacity:.48' : '' }}">
                                <div class="severity-name">{!! log_styler()->icon($severity) !!} {{ $item['name'] }}</div>
                                <div class="severity-meter"><span style="width:{{ ((int) $item['count'] / $maxLevelCount) * 100 }}%"></span></div>
                                <div class="severity-value"><b>{{ number_format($item['count']) }}</b><span>{{ number_format((float) $item['percent'], 1) }}%</span></div>
                            </div>
                        @endforeach
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
ready(function () {
    if (typeof Chart === 'undefined') return;
    var charts = [];
    var trend = @json($trendDatasets), labels = @json($trendLabels);
    function colors() {
        var s = getComputedStyle(document.documentElement);
        return { text:s.getPropertyValue('--muted').trim(), grid:s.getPropertyValue('--grid').trim(),
            levels:Object.fromEntries(@json($levels).map(function(l){return [l,s.getPropertyValue('--lvl-'+l).trim()] })) };
    }
    function draw() {
        charts.forEach(function(c){c.destroy()}); charts=[]; var c=colors();
        charts.push(new Chart(document.getElementById('trend-chart'), {type:'line',data:{labels:labels,datasets:trend.map(function(d){return {label:d.label,data:d.data,borderColor:c.levels[d.level],backgroundColor:c.levels[d.level],borderWidth:d.level==='error'?3:1.75,pointRadius:2,pointHoverRadius:5,tension:.28,fill:false}})},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},scales:{x:{ticks:{color:c.text,maxRotation:0},grid:{display:false}},y:{beginAtZero:true,ticks:{color:c.text,precision:0},grid:{color:c.grid}}},plugins:{legend:{position:'bottom',labels:{color:c.text,usePointStyle:true,boxWidth:7}}}}}));
    }
    draw();
    new MutationObserver(draw).observe(document.documentElement,{attributes:true,attributeFilter:['data-theme']});
    matchMedia('(prefers-color-scheme:dark)').addEventListener('change',function(){if(document.documentElement.dataset.theme==='auto')draw()});
});
</script>
@endsection
