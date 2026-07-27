@extends('log-viewer::console._master')
@section('title', 'LogViewer — Dashboard')

<?php
    /** Analytics computed straight from the stats table (per-date rows + totals). */
    $stats   = log_viewer()->statsTable();
    $rows    = $stats->rows();                 // [date => ['date'=>.., all=>.., emergency=>.., ...]]
    $footer  = $stats->footer();               // [level => total], incl 'all'
    $names   = $stats->header();               // [level => Localised name], incl 'date','all'

    $order       = ['emergency','alert','critical','error','warning','notice','info','debug'];
    $totalAll    = (int) ($footer['all'] ?? 0);
    $fileCount   = count($rows);
    $errorFamily = ['emergency','alert','critical','error'];
    $errorTotal  = array_sum(array_map(fn ($l) => (int) ($footer[$l] ?? 0), $errorFamily));
    $errorRate   = $totalAll ? round(($errorTotal / $totalAll) * 100, 1) : 0;

    // Error-over-time trend: most recent ~30 files, oldest→newest so the current
    // day sits on the right edge. Errors (emergency→error) vs warnings vs total.
    $trendRows = array_slice($rows, 0, 30, true);
    $trendRows = array_reverse($trendRows, true);
    $trend = ['labels' => [], 'errors' => [], 'warning' => [], 'all' => []];
    foreach ($trendRows as $date => $row) {
        $trend['labels'][]  = $date;
        $trend['errors'][]  = array_sum(array_map(fn ($l) => (int) ($row[$l] ?? 0), $errorFamily));
        $trend['warning'][] = (int) ($row['warning'] ?? 0);
        $trend['all'][]     = (int) ($row['all'] ?? 0);
    }
    $todayErrors = end($trend['errors']) ?: 0;
    $prevErrors  = count($trend['errors']) > 1 ? $trend['errors'][count($trend['errors']) - 2] : 0;
    $errorDelta  = $todayErrors - $prevErrors;
?>

@section('content')
    <h1 class="page-title">@lang('Dashboard')</h1>
    <p class="page-sub">@lang('An overview of every log level across all files.')</p>

    {{-- KPI tiles --}}
    <div class="kpi-grid">
        <div class="kpi">
            <span class="kpi-ico" style="--c:var(--brand);"><i class="fa fa-database"></i></span>
            <span class="kpi-label">@lang('Total entries')</span>
            <span class="kpi-value">{{ number_format($totalAll) }}</span>
            <span class="kpi-sub">@lang('across all levels')</span>
        </div>
        <div class="kpi">
            <span class="kpi-ico" style="--c:var(--lvl-info);"><i class="fa fa-files-o"></i></span>
            <span class="kpi-label">@lang('Log files')</span>
            <span class="kpi-value">{{ number_format($fileCount) }}</span>
            <span class="kpi-sub">@lang('being tracked')</span>
        </div>
        <div class="kpi">
            <span class="kpi-ico" style="--c:var(--lvl-error);"><i class="fa fa-times-circle"></i></span>
            <span class="kpi-label">@lang('Errors')</span>
            <span class="kpi-value" style="--c:var(--lvl-error);">{{ number_format($errorTotal) }}</span>
            <span class="kpi-sub">@lang('emergency → error')</span>
        </div>
        <div class="kpi">
            <span class="kpi-ico" style="--c:var(--lvl-warning);"><i class="fa fa-percent"></i></span>
            <span class="kpi-label">@lang('Error rate')</span>
            <span class="kpi-value" style="--c:{{ $errorRate >= 10 ? 'var(--lvl-error)' : 'var(--text)' }};">{{ $errorRate }}%</span>
            <span class="kpi-sub">@lang('of all entries')</span>
        </div>
    </div>

    {{-- Trend + distribution --}}
    <div class="dash-grid">
        <div class="chart-card">
            <div class="ch-head">
                <span class="ch-title">@lang('Errors over time')</span>
                <span class="page-sub" style="margin:0;">
                    @lang('latest') <b style="color:var(--lvl-error);">{{ number_format($todayErrors) }}</b>
                    @if($errorDelta !== 0)
                        <span style="color:{{ $errorDelta > 0 ? 'var(--lvl-error)' : 'var(--lvl-notice)' }};">
                            <i class="fa fa-arrow-{{ $errorDelta > 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($errorDelta)) }}
                        </span>
                    @endif
                    @lang('vs previous')
                </span>
            </div>
            <div class="ch-body" style="align-items:stretch;">
                <canvas id="trend"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="ch-head"><span class="ch-title">@lang('Level distribution')</span></div>
            <div class="ch-body"><canvas id="chart" height="240"></canvas></div>
        </div>
    </div>

    {{-- Level breakdown --}}
    <div class="card" style="margin-top:16px;">
        <div class="card-head"><span class="card-title">@lang('Breakdown by level')</span></div>
        <div class="lvl-list">
            @foreach($order as $level)
                @php $count = (int) ($footer[$level] ?? 0);
                     $pct   = $totalAll ? round(($count / $totalAll) * 100, 1) : 0; @endphp
                <div class="lvl-row lvl-{{ $level }}" style="{{ $count === 0 ? 'opacity:.5;' : '' }}">
                    <span class="name">{!! log_styler()->icon($level) !!} {{ $names[$level] ?? ucfirst($level) }}</span>
                    <span class="bar"><i style="width:{{ max($pct, $count ? 2 : 0) }}%"></i></span>
                    <span class="ct">{{ number_format($count) }}</span>
                    <span class="pc">{{ $pct }}%</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
ready(function () {
    if (typeof Chart === 'undefined') return;
    var css = getComputedStyle(document.documentElement);
    var muted = css.getPropertyValue('--muted').trim();
    var grid  = css.getPropertyValue('--border').trim();
    var lvlColor = function (l) { return css.getPropertyValue('--lvl-' + l).trim() || css.getPropertyValue('--brand').trim(); };

    Chart.defaults.color = muted;
    Chart.defaults.font.family = css.getPropertyValue('--sans').trim() || 'sans-serif';

    // Distribution doughnut
    new Chart(document.getElementById('chart'), {
        type: 'doughnut',
        data: {!! $chartData !!},
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } } },
            cutout: '64%'
        }
    });

    // Errors-over-time line chart. Errors are the focus (bold, filled area);
    // warnings and total give context as lighter lines.
    var trend = {!! json_encode($trend) !!};
    var ctx = document.getElementById('trend').getContext('2d');
    function hexA(hex, a) {
        hex = (hex || '').replace('#', '').trim();
        if (hex.length === 3) hex = hex.split('').map(function (c) { return c + c; }).join('');
        if (hex.length !== 6) return 'rgba(224,87,75,' + a + ')';
        var n = parseInt(hex, 16);
        return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + a + ')';
    }
    function fill(hex) {
        // Soft gradient under a line, from the line colour down to transparent.
        var g = ctx.createLinearGradient(0, 0, 0, 260);
        g.addColorStop(0, hexA(hex, .28));
        g.addColorStop(1, hexA(hex, 0));
        return g;
    }
    var cError = lvlColor('error'), cWarn = lvlColor('warning');
    new Chart(document.getElementById('trend'), {
        type: 'line',
        data: {
            labels: trend.labels,
            datasets: [
                { label: 'Errors', data: trend.errors, borderColor: cError, backgroundColor: fill(cError),
                  borderWidth: 2.5, fill: true, tension: .35, pointRadius: 2, pointHoverRadius: 5,
                  pointBackgroundColor: cError, order: 1 },
                { label: 'Warnings', data: trend.warning, borderColor: cWarn, backgroundColor: 'transparent',
                  borderWidth: 1.5, fill: false, tension: .35, pointRadius: 0, pointHoverRadius: 4, order: 2 },
                { label: 'Total', data: trend.all, borderColor: muted, backgroundColor: 'transparent',
                  borderWidth: 1, borderDash: [4, 4], fill: false, tension: .35, pointRadius: 0, pointHoverRadius: 4, order: 3 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, usePointStyle: true } },
                tooltip: { padding: 10, boxPadding: 4 }
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
                y: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } }
            }
        }
    });
});
</script>
@endsection
