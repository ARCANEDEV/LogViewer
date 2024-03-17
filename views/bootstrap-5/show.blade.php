<?php
/**
 * @var  Arcanedev\LogViewer\Entities\Log            $log
 * @var  Illuminate\Pagination\LengthAwarePaginator  $entries
 * @var  string|null                                 $query
 */
?>

@extends('log-viewer::bootstrap-5._master')

@section('content')
    <div class="page-header mb-4">
        <h1>@lang('Log') [{{ $log->date }}]</h1>
    </div>

    <div class="row">
        <div class="col-lg-2">
            {{-- Log Menu --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fa fa-fw fa-flag"></i> @lang('Levels')</div>
                <div class="list-group list-group-flush log-menu">
                    @foreach($log->menu() as $levelKey => $item)
                        @if ($item['count'] === 0)
                            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center disabled">
                                <span class="level-name">{!! $item['icon'] !!} {{ $item['name'] }}</span>
                                <span class="badge empty">{{ $item['count'] }}</span>
                            </a>
                        @else
                            <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center level-{{ $levelKey }}{{ $level === $levelKey ? ' active' : ''}}">
                                <span class="level-name">{!! $item['icon'] !!} {{ $item['name'] }}</span>
                                <span class="badge badge-level-{{ $levelKey }}">{{ $item['count'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-10">
            {{-- Log Details --}}
            <div class="card mb-4">
                <div class="card-header">
                    @lang('Log info') :
                    <div class="group-btns pull-right">
                        <a href="{{ route('log-viewer::logs.download', [$log->date]) }}" class="btn btn-sm btn-success">
                            <i class="fa fa-download"></i> @lang('Download')
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete-log-modal">
                            <i class="fa fa-trash-o"></i> @lang('Delete')
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed mb-0">
                        <tbody>
                            <tr>
                                <td>@lang('File path') :</td>
                                <td colspan="7">{{ $log->getPath() }}</td>
                            </tr>
                            <tr>
                                <td>@lang('Log entries') :</td>
                                <td>
                                    <span class="badge text-bg-primary">{{ $entries->total() }}</span>
                                </td>
                                <td>@lang('Size') :</td>
                                <td>
                                    <span class="badge text-bg-primary">{{ $log->size() }}</span>
                                </td>
                                <td>@lang('Created at') :</td>
                                <td>
                                    <span class="badge text-bg-primary">{{ $log->createdAt() }}</span>
                                </td>
                                <td>@lang('Updated at') :</td>
                                <td>
                                    <span class="badge text-bg-primary">{{ $log->updatedAt() }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{-- Search --}}
                    <form action="{{ route('log-viewer::logs.search', [$log->date, $level]) }}" method="GET">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="search" id="query" name="query" class="form-control" value="{{ $query }}" placeholder="@lang('Type here to search')">
                                @unless (is_null($query))
                                    <a href="{{ route('log-viewer::logs.show', [$log->date]) }}" class="btn btn-light">
                                        (@lang(':count results', ['count' => $entries->count()])) <i class="fa fa-fw fa-times"></i>
                                    </a>
                                @endunless
                                <button id="search-btn" class="btn btn-primary">
                                    <span class="fa fa-fw fa-search"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Log Entries --}}
            <div class="card mb-4">
                @if ($entries->hasPages())
                    <div class="card-header">
                        <span class="badge text-bg-info float-right">
                            {{ __('Page :current of :last', ['current' => $entries->currentPage(), 'last' => $entries->lastPage()]) }}
                        </span>
                    </div>
                @endif

                <div class="table-responsive">
                    <table id="entries" class="table mb-0">
                        <thead>
                            <tr>
                                <th>@lang('ENV')</th>
                                <th style="width: 120px;">@lang('Level')</th>
                                <th style="width: 65px;">@lang('Time')</th>
                                <th>@lang('Header')</th>
                                <th class="text-end">@lang('Actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $key => $entry)
                                <?php /** @var  Arcanedev\LogViewer\Entities\LogEntry  $entry */ ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-env">{{ $entry->env }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-level-{{ $entry->level }}">
                                            {!! $entry->level() !!}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-secondary">
                                            {{ $entry->datetime->format('H:i:s') }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $entry->header }}
                                    </td>
                                    <td class="text-end">
                                        @if ($entry->hasStack())
                                        <a class="btn btn-sm btn-light" role="button" data-bs-toggle="collapse"
                                           href="#log-stack-{{ $key }}" aria-expanded="false" aria-controls="log-stack-{{ $key }}">
                                            <i class="fa fa-toggle-on"></i> @lang('Stack')
                                        </a>
                                        @endif

                                        @if ($entry->hasContext())
                                        <a class="btn btn-sm btn-light" role="button" data-bs-toggle="collapse"
                                           href="#log-context-{{ $key }}" aria-expanded="false" aria-controls="log-context-{{ $key }}">
                                            <i class="fa fa-toggle-on"></i> @lang('Context')
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @if ($entry->hasStack() || $entry->hasContext())
                                    <tr>
                                        <td colspan="5" class="stack py-0">
                                            @if ($entry->hasStack())
                                            <div class="stack-content collapse" id="log-stack-{{ $key }}">
                                                {!! $entry->stack() !!}
                                            </div>
                                            @endif

                                            @if ($entry->hasContext())
                                            <div class="stack-content collapse" id="log-context-{{ $key }}">
                                                <pre>{{ $entry->context() }}</pre>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <span class="badge text-bg-secondary">@lang('The list of logs is empty!')</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {!! $entries->appends(compact('query'))->render() !!}
        </div>
    </div>
@endsection

@section('modals')
    {{-- DELETE MODAL --}}
    <div class="modal fade" id="delete-log-modal" tabindex="-1" aria-labelledby="delete-log-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <input type="hidden" name="date" value="{{ $log->date }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fs-5" id="delete-log-modal-label">@lang('Delete log file')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>@lang('Are you sure you want to delete this log file: :date ?', ['date' => $log->date])</p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="@lang('Loading')&hellip;">@lang('Delete')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        ready(() => {
            let deleteLogModal = new bootstrap.Modal('div#delete-log-modal')
            let deleteLogForm = document.querySelector('form#delete-log-form')
            let submitBtn = new bootstrap.Button(deleteLogForm.querySelector('button[type=submit]'))

            deleteLogForm.addEventListener('submit', (event) => {
                event.preventDefault()
                submitBtn.toggle('loading')

                fetch(event.currentTarget.getAttribute('action'), {
                    method: 'DELETE',
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        'Content-type': 'application/json'
                    },
                    body: JSON.stringify({
                        date: event.currentTarget.querySelector("input[name='date']").value,
                    })
                })
                    .then((resp) => resp.json())
                    .then((resp) => {
                        if (resp.result === 'success') {
                            deleteLogModal.hide();
                            location.replace("{{ route('log-viewer::logs.list') }}");
                        }
                        else {
                            alert('AJAX ERROR ! Check the console !')
                            console.error(resp)
                        }
                    })
                    .catch((err) => {
                        alert('AJAX ERROR ! Check the console !')
                        console.error(err)
                    })

                return false
            })

            @unless (empty(log_styler()->toHighlight()))
                @php
                    $htmlHighlight = version_compare(PHP_VERSION, '7.4.0') >= 0
                        ? join('|', log_styler()->toHighlight())
                        : join(log_styler()->toHighlight(), '|');
                @endphp

                document.querySelectorAll('.stack-content').forEach((elt) => {
                    elt.innerHTML = elt.innerHTML.trim()
                        .replace(/({!! $htmlHighlight !!})/gm, '<strong>$1</strong>')
                })
            @endunless
        });
    </script>
@endsection
