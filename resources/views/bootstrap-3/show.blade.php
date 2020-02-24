<?php
/**
 * @var  Arcanedev\LogViewer\Entities\Log            $log
 * @var  Illuminate\Pagination\LengthAwarePaginator  $entries
 * @var  string|null                                 $query
 */
?>

@extends('log-viewer::bootstrap-3._master')

@section('content')
    <h1 class="page-header">@lang('Log') [{{ $log->date }}]</h1>

    <div class="row">
        <div class="col-md-2">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-fw fa-flag"></i> @lang('Levels')</div>
                <ul class="list-group">
                    @foreach($log->menu() as $levelKey => $item)
                        @if ($item['count'] === 0)
                            <a href="#" class="list-group-item disabled">
                                <span class="badge">
                                    {{ $item['count'] }}
                                </span>
                                {!! $item['icon'] !!} {{ $item['name'] }}
                            </a>
                        @else
                            <a href="{{ $item['url'] }}" class="list-group-item {{ $levelKey }}">
                                <span class="badge level-{{ $levelKey }}">
                                    {{ $item['count'] }}
                                </span>
                                <span class="level level-{{ $levelKey }}">
                                    {!! $item['icon'] !!} {{ $item['name'] }}
                                </span>
                            </a>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-md-10">
            {{-- Log Details --}}
            <div class="panel panel-default">
                <div class="panel-heading">
                    @lang('Log info') :

                    <div class="group-btns pull-right">
                        <a href="{{ route('log-viewer::logs.download', [$log->date]) }}" class="btn btn-xs btn-success">
                            <i class="fa fa-download"></i> @lang('Download')
                        </a>
                        <a href="#delete-log-modal" class="btn btn-xs btn-danger" data-toggle="modal">
                            <i class="fa fa-trash-o"></i> @lang('Delete')
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <td>@lang('File path') :</td>
                                <td colspan="5">{{ $log->getPath() }}</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>@lang('Log entries') :</td>
                                <td>
                                    <span class="label label-primary">{{ $entries->total() }}</span>
                                </td>
                                <td>@lang('Size') :</td>
                                <td>
                                    <span class="label label-primary">{{ $log->size() }}</span>
                                </td>
                                <td>@lang('Created at') :</td>
                                <td>
                                    <span class="label label-primary">{{ $log->createdAt() }}</span>
                                </td>
                                <td>@lang('Updated at') :</td>
                                <td>
                                    <span class="label label-primary">{{ $log->updatedAt() }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    {{-- Search --}}
                    <form action="{{ route('log-viewer::logs.search', [$log->date, $level]) }}" method="GET">
                        <div class=form-group">
                            <div class="input-group">
                                <input id="query" name="query" class="form-control" value="{{ $query }}" placeholder="@lang('Type here to search')">
                                <span class="input-group-btn">
                                    @unless (is_null($query))
                                        <a href="{{ route('log-viewer::logs.show', [$log->date]) }}" class="btn btn-default">
                                            (@lang(':count results', ['count' => $entries->count()])) <span class="glyphicon glyphicon-remove"></span>
                                        </a>
                                    @endunless
                                    <button id="search-btn" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-search"></span>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Log Entries --}}
            <div class="panel panel-default">
                @if ($entries->hasPages())
                    <div class="panel-heading">
                        {{ $entries->appends(compact('query'))->render() }}

                        <span class="label label-info pull-right">
                            {{ __('Page :current of :last', ['current' => $entries->currentPage(), 'last' => $entries->lastPage()]) }}
                        </span>
                    </div>
                @endif

                <div class="table-responsive">
                    <table id="entries" class="table table-condensed">
                        <thead>
                            <tr>
                                <th>@lang('ENV')</th>
                                <th style="width: 120px;">@lang('Level')</th>
                                <th style="width: 65px;">@lang('Time')</th>
                                <th>@lang('Header')</th>
                                <th class="text-right">@lang('Actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $key => $entry)
                                <?php /** @var  Arcanedev\LogViewer\Entities\LogEntry  $entry */ ?>
                                <tr>
                                    <td>
                                        <span class="label label-env">{{ $entry->env }}</span>
                                    </td>
                                    <td>
                                        <span class="level level-{{ $entry->level }}">{!! $entry->level() !!}</span>
                                    </td>
                                    <td>
                                        <span class="label label-default">
                                            {{ $entry->datetime->format('H:i:s') }}
                                        </span>
                                    </td>
                                    <td>
                                        <p>{{ $entry->header }}</p>
                                    </td>
                                    <td class="text-right">
                                        @if ($entry->hasStack())
                                        <a class="btn btn-xs btn-default" role="button" data-toggle="collapse"
                                           href="#log-stack-{{ $key }}" aria-expanded="false" aria-controls="log-stack-{{ $key }}">
                                            <i class="fa fa-toggle-on"></i> @lang('Stack')
                                        </a>
                                        @endif

                                        @if ($entry->hasContext())
                                        <a class="btn btn-xs btn-default" role="button" data-toggle="collapse"
                                           href="#log-context-{{ $key }}" aria-expanded="false" aria-controls="log-context-{{ $key }}">
                                            <i class="fa fa-toggle-on"></i> @lang('Context')
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @if ($entry->hasStack() || $entry->hasContext())
                                    <tr>
                                        <td colspan="5" class="stack">
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
                                        <span class="label label-default">@lang('The list of logs is empty!')</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($entries->hasPages())
                    <div class="panel-footer">
                        {!! $entries->appends(compact('query'))->render() !!}

                        <span class="label label-info pull-right">
                            @lang('Page :current of :last', ['current' => $entries->currentPage(), 'last' => $entries->lastPage()])
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('modals')
    {{-- DELETE MODAL --}}
    <div id="delete-log-modal" class="modal fade">
        <div class="modal-dialog">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="date" value="{{ $log->date }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('Delete log file')</h4>
                    </div>
                    <div class="modal-body">
                        <p>@lang('Are you sure you want to delete this log file: :date ?', ['date' => $log->date])</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-default pull-left" data-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="@lang('Loading')&hellip;">@lang('Delete')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            var deleteLogModal = $('div#delete-log-modal'),
                deleteLogForm  = $('form#delete-log-form'),
                submitBtn      = deleteLogForm.find('button[type=submit]');

            deleteLogForm.on('submit', function(event) {
                event.preventDefault();
                submitBtn.button('loading');

                $.ajax({
                    url:      $(this).attr('action'),
                    type:     $(this).attr('method'),
                    dataType: 'json',
                    data:     $(this).serialize(),
                    success: function(data) {
                        submitBtn.button('reset');
                        if (data.result === 'success') {
                            deleteLogModal.modal('hide');
                            location.replace("{{ route('log-viewer::logs.list') }}");
                        }
                        else {
                            alert('OOPS ! This is a lack of coffee exception !')
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        alert('AJAX ERROR ! Check the console !');
                        console.error(errorThrown);
                        submitBtn.button('reset');
                    }
                });

                return false;
            });

            @unless (empty(log_styler()->toHighlight()))
                @php
                    $htmlHighlight = version_compare(PHP_VERSION, '7.4.0') >= 0
                        ? join('|', log_styler()->toHighlight())
                        : join(log_styler()->toHighlight(), '|');
                @endphp
                $('.stack-content').each(function() {
                    var $this = $(this);
                    var html = $this.html().trim()
                        .replace(/({!! $htmlHighlight !!})/gm, '<strong>$1</strong>');

                    $this.html(html);
                });
            @endunless
        });
    </script>
@endsection
