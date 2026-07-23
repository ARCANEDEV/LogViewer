@extends('log-viewer::console._master')
@section('title', 'LogViewer — Logs')

<?php
    /** @var Illuminate\Pagination\LengthAwarePaginator $rows */
    $order = ['emergency','alert','critical','error','warning','notice','info','debug'];
?>

@section('content')
    <h1 class="page-title">@lang('Logs')</h1>
    <p class="page-sub">@lang('All log files, with a per-level breakdown.')</p>

    <div class="card">
        <div class="toolbar">
            <span class="search-wrap" style="max-width:280px;">
                <i class="fa fa-filter"></i>
                <input class="input" id="quick-filter" type="search" placeholder="@lang('Filter by date')…" autocomplete="off">
            </span>
            <span class="spacer" style="flex:1;"></span>
            <span class="sev-legend">
                @foreach(['error'=>'Errors','warning'=>'Warnings','info'=>'Info','debug'=>'Other'] as $k => $lbl)
                    <span class="sev-legend" style="margin-left:10px;"><span class="dot lvl-{{ $k }}" style="background:var(--lvl-{{ $k }});"></span>{{ $lbl }}</span>
                @endforeach
            </span>
        </div>

        <div style="overflow-x:auto;">
            <table class="grid">
                <thead>
                    <tr>
                        <th>@lang('Date')</th>
                        <th style="text-align:right;">@lang('Entries')</th>
                        <th style="min-width:160px;">@lang('Composition')</th>
                        <th>@lang('Levels')</th>
                        <th style="text-align:right;">@lang('Actions')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $date => $row)
                        @php $all = (int) ($row['all'] ?? 0); @endphp
                        <tr data-search="{{ strtolower($date) }}">
                            <td>
                                <a href="{{ route('log-viewer::logs.show', [$date]) }}" class="mono"><b>{{ $date }}</b></a>
                            </td>
                            <td style="text-align:right;"><span class="cell-num">{{ number_format($all) }}</span></td>
                            <td>
                                <span class="sev-bar" title="{{ $all }} @lang('entries')">
                                    @foreach($order as $level)
                                        @php $c = (int) ($row[$level] ?? 0); @endphp
                                        @if ($c > 0)
                                            <span style="width:{{ $all ? ($c / $all) * 100 : 0 }}%; background:var(--lvl-{{ $level }});"
                                                  title="{{ ucfirst($level) }}: {{ number_format($c) }}"></span>
                                        @endif
                                    @endforeach
                                </span>
                            </td>
                            <td>
                                <span class="chipbar">
                                    @foreach($order as $level)
                                        @php $c = (int) ($row[$level] ?? 0); @endphp
                                        @if ($c > 0)
                                            <a class="chip lvl lvl-{{ $level }}" href="{{ route('log-viewer::logs.filter', [$date, $level]) }}"
                                               title="{{ ucfirst($level) }}">
                                                {!! log_styler()->icon($level) !!} <span class="n">{{ number_format($c) }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                    @if ($all === 0)<span style="color:var(--muted); opacity:.6;">—</span>@endif
                                </span>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a class="btn btn-sm" href="{{ route('log-viewer::logs.show', [$date]) }}" title="@lang('View')"><i class="fa fa-eye"></i></a>
                                <a class="btn btn-sm" href="{{ route('log-viewer::logs.download', [$date]) }}" title="@lang('Download')"><i class="fa fa-download"></i></a>
                                <button class="btn btn-sm btn-danger" data-delete="{{ $date }}" title="@lang('Delete')"><i class="fa fa-trash-o"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><i class="fa fa-inbox fa-2x"></i><p>@lang('The list of logs is empty!')</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
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
@endsection

@section('scripts')
<script>
ready(function () {
    var qf = document.getElementById('quick-filter');
    qf.addEventListener('input', function () {
        var term = qf.value.toLowerCase().trim();
        document.querySelectorAll('tbody tr[data-search]').forEach(function (row) {
            row.style.display = (!term || row.dataset.search.indexOf(term) !== -1) ? '' : 'none';
        });
    });

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
});
</script>
@endsection
