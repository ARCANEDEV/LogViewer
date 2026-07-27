@extends('log-viewer::observatory._master')
@section('title', 'LogViewer — Logs')

<?php /** @var Illuminate\Pagination\LengthAwarePaginator $rows */ ?>

@section('content')
    <h1 class="page-title">@lang('Log files')</h1>
    <p class="page-sub">@lang('Scan volume and severity composition before opening an investigation.')</p>

    <div class="card">
        <div class="toolbar">
            <span class="search-wrap" style="max-width:280px;">
                <i class="fa fa-filter"></i>
                <input class="input" id="quick-filter" type="search" placeholder="@lang('Filter by date')…" autocomplete="off">
            </span>
            <span class="filter-result" id="filter-result" aria-live="polite"></span>
        </div>

        <div class="file-grid" id="file-grid">
            @forelse($rows as $date => $row)
                @php
                    $rowTotal = 0; $rowParts = []; $levelCounts = []; $rowDanger = 0;
                    foreach ($row as $rowKey => $rowValue) {
                        if ($rowKey === 'date') continue;
                        $rowTotal += (int) $rowValue;
                        if ((int) $rowValue > 0) $levelCounts[$rowKey] = (int) $rowValue;
                        if (in_array($rowKey, ['emergency','alert','critical','error'], true)) $rowDanger += (int) $rowValue;
                    }
                    foreach ($row as $rowKey => $rowValue) if ($rowKey !== 'date' && (int) $rowValue > 0) $rowParts[] = ucfirst($rowKey).': '.number_format((int) $rowValue);
                    arsort($levelCounts);
                    $topLevels = array_slice($levelCounts, 0, 4, true);
                    $riskRate = $rowTotal > 0 ? ($rowDanger / $rowTotal) * 100 : 0;
                    $rowSummary = $date.' — '.number_format($rowTotal).' events'.($rowParts ? ' ('.implode(', ', $rowParts).')' : '');
                @endphp
                <article class="file-card" data-search="{{ strtolower($date) }}">
                    <div class="file-date">
                        <a href="{{ route('log-viewer::logs.show', [$date]) }}" class="date-link">{{ $date }}</a>
                        <span>{{ number_format($rowTotal) }} @lang('events')</span>
                    </div>
                    <div class="file-signal">
                        <div class="file-signal-head">
                            <span>@lang('Severity composition')</span>
                            <span class="{{ $rowDanger > 0 ? 'lvl-error' : 'lvl-info' }}" style="color:var(--c)"><b>{{ number_format($rowDanger) }}</b> @lang('high severity') · {{ number_format($riskRate, 1) }}%</span>
                        </div>
                        <div class="severity-track">
                            @if($rowTotal > 0)
                                @foreach($row as $mixKey => $mixValue)
                                    @if($mixKey !== 'date' && (int) $mixValue > 0)
                                        <span class="lvl-{{ $mixKey }}" style="width:{{ ((int) $mixValue / $rowTotal) * 100 }}%;background:var(--c)"></span>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="signal-levels">
                            @forelse($topLevels as $levelKey => $value)
                                <a class="lvl-{{ $levelKey }}" href="{{ route('log-viewer::logs.filter', [$date, $levelKey]) }}">{!! log_styler()->icon($levelKey) !!} {{ ucfirst($levelKey) }} <b>{{ number_format($value) }}</b></a>
                            @empty
                                <span>@lang('No events recorded')</span>
                            @endforelse
                            @if(count($levelCounts) > 4)<span class="more-levels">+{{ count($levelCounts) - 4 }} @lang('more')</span>@endif
                        </div>
                    </div>
                    <div class="file-card-actions">
                        <a class="btn btn-sm btn-primary" href="{{ route('log-viewer::logs.show', [$date]) }}"><i class="fa fa-arrow-right"></i> @lang('Investigate')</a>
                        <button class="btn btn-sm copy-icon" data-copy-text="{{ e($rowSummary) }}" title="@lang('Copy summary')"><i class="fa fa-clipboard"></i></button>
                        <a class="btn btn-sm copy-icon" href="{{ route('log-viewer::logs.download', [$date]) }}" title="@lang('Download')"><i class="fa fa-download"></i></a>
                        @if (config('log-viewer.delete', false))
                            <button class="btn btn-sm copy-icon file-delete" data-delete="{{ $date }}" title="@lang('Delete log')"><i class="fa fa-trash-o"></i></button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty-state" style="grid-column:1/-1"><i class="fa fa-inbox fa-2x"></i><p>@lang('The list of logs is empty!')</p></div>
            @endforelse
        </div>

        @if ($rows->lastPage() > 1)
            <div class="pager">
                <span>@lang('Page') {{ $rows->currentPage() }} / {{ $rows->lastPage() }}</span>
                <span class="pages">
                    <a class="btn btn-sm" href="{{ $rows->previousPageUrl() ?: '#' }}" @if($rows->onFirstPage()) style="opacity:.4; pointer-events:none;" @endif><i class="fa fa-angle-left"></i> @lang('Prev')</a>
                    <a class="btn btn-sm" href="{{ $rows->nextPageUrl() ?: '#' }}" @if(! $rows->hasMorePages()) style="opacity:.4; pointer-events:none;" @endif>@lang('Next') <i class="fa fa-angle-right"></i></a>
                </span>
            </div>
        @endif
    </div>
@endsection

@section('modals')
    @if (config('log-viewer.delete', false))
        <div class="modal-overlay" id="delete-modal">
            <div class="modal">
                <h3>@lang('Delete log file')</h3>
                <div class="body" id="delete-msg"></div>
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
    var qf = document.getElementById('quick-filter');
    qf.addEventListener('input', function () {
        var term = qf.value.toLowerCase().trim();
        var visible = 0, total = 0;
        document.querySelectorAll('#file-grid [data-search]').forEach(function (row) {
            var show = !term || row.dataset.search.indexOf(term) !== -1;
            row.style.display = show ? '' : 'none';
            total++; if (show) visible++;
        });
        var result = document.getElementById('filter-result');
        result.textContent = term ? visible + ' / ' + total + ' files shown' : '';
        result.style.display = term ? 'inline' : 'none';
    });

@if (config('log-viewer.delete', false))
    var modal = document.getElementById('delete-modal'), target = null;
    var msgTpl = "{{ __('Are you sure you want to delete this log file: :date ?') }}";
    document.querySelectorAll('[data-delete]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            target = btn.dataset.delete;
            document.getElementById('delete-msg').textContent = msgTpl.replace(':date', target);
            modal.classList.add('show');
        });
    });
    document.getElementById('delete-cancel').addEventListener('click', function () { modal.classList.remove('show'); });
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('show'); });
    document.getElementById('delete-confirm').addEventListener('click', function () {
        fetch("{{ route('log-viewer::logs.delete') }}", {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ date: target })
        }).then(function (r) { return r.json(); }).then(function (r) {
            if (r.result === 'success') location.reload();
            else alert('Delete failed — check the console.');
        }).catch(function (e) { alert('Delete failed — check the console.'); console.error(e); });
    });
@endif
});
</script>
@endsection
