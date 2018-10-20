<?php
/**
 * @var  Arcanedev\LogViewer\Entities\Log            $log
 * @var  Illuminate\Pagination\LengthAwarePaginator  $entries
 * @var  string|null                                 $query
 */
?>

@extends('log-viewer::bootstrap-4._master')

@section('content')
    <div class="page-header mb-4">
        <h1>{{ trans('log-viewer::show.log.header') }} [{{ $log->date }}]</h1>
    </div>

    <div class="row">
        <div class="col-lg-2">
            {{-- Log Menu --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fa fa-fw fa-flag"></i> {{ trans('log-viewer::show.levels') }}</div>
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
                    {{ trans('log-viewer::show.log.info') }} :
                    <div class="group-btns pull-right">
                        <a href="{{ route('log-viewer::logs.download', [$log->date]) }}" class="btn btn-sm btn-success">
                            <i class="fa fa-download"></i> {{ trans('log-viewer::show.log.download') }}
                        </a>
                        <a href="#delete-log-modal" class="btn btn-sm btn-danger" data-toggle="modal">
                            <i class="fa fa-trash-o"></i> {{ trans('log-viewer::show.log.delete') }}
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed mb-0">
                        <tbody>
                            <tr>
                                <td>{{ trans('log-viewer::show.log.path') }} :</td>
                                <td colspan="7">{{ $log->getPath() }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('log-viewer::show.log.entries') }} : </td>
                                <td>
                                    <span class="badge badge-primary">{{ $entries->total() }}</span>
                                </td>
                                <td>{{ trans('log-viewer::show.log.size') }} :</td>
                                <td>
                                    <span class="badge badge-primary">{{ $log->size() }}</span>
                                </td>
                                <td>{{ trans('log-viewer::show.log.created_at') }} :</td>
                                <td>
                                    <span class="badge badge-primary">{{ $log->createdAt() }}</span>
                                </td>
                                <td>{{ trans('log-viewer::show.log.updated_at') }} :</td>
                                <td>
                                    <span class="badge badge-primary">{{ $log->updatedAt() }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{-- Search --}}
                    <form action="{{ route('log-viewer::logs.search', [$log->date, $level]) }}" method="GET">
                        <div class=form-group">
                            <div class="input-group">
                                <input id="query" name="query" class="form-control" value="{!! $query !!}" placeholder="{{ trans('log-viewer::show.log.search.placeholder') }}">
                                <div class="input-group-append">
                                    @unless (is_null($query))
                                        <a href="{{ route('log-viewer::logs.show', [$log->date]) }}" class="btn btn-secondary">
                                            <i class="fa fa-fw fa-times"></i>
                                        </a>
                                    @endunless
                                    <button id="search-btn" class="btn btn-primary">
                                        <span class="fa fa-fw fa-search"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Log Entries --}}
            <div class="card mb-4">
                @if ($entries->hasPages())
                    <div class="card-header">
                        <span class="badge badge-info float-right">
                            {{ trans('log-viewer::show.page.page') }} {{ $entries->currentPage() }} {{ trans('log-viewer::show.page.of') }} {{ $entries->lastPage() }}
                        </span>
                    </div>
                @endif

                <div class="table-responsive">
                    <table id="entries" class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ trans('log-viewer::show.log.env') }}</th>
                                <th style="width: 120px;">{{ trans('log-viewer::show.log.level') }}</th>
                                <th style="width: 65px;">{{ trans('log-viewer::show.log.time') }}</th>
                                <th>{{ trans('log-viewer::show.log.table_header') }}</th>
                                <th class="text-right">{{ trans('log-viewer::show.log.actions') }}</th>
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
                                        <span class="badge badge-secondary">
                                            {{ $entry->datetime->format('H:i:s') }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $entry->header }}
                                    </td>
                                    <td class="text-right">
                                        @if ($entry->hasStack())
                                            <a class="btn btn-sm btn-light" role="button" data-toggle="collapse" href="#log-stack-{{ $key }}" aria-expanded="false" aria-controls="log-stack-{{ $key }}">
                                                <i class="fa fa-toggle-on"></i> {{ trans('log-viewer::show.log.stack') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @if ($entry->hasStack())
                                    <tr>
                                        <td colspan="5" class="stack py-0">
                                            <div class="stack-content collapse" id="log-stack-{{ $key }}">
                                                {!! $entry->stack() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <span class="badge badge-secondary">{{ trans('log-viewer::general.empty-logs') }}</span>
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
    <div id="delete-log-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="date" value="{{ $log->date }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('log-viewer::show.modals.delete.header') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('log-viewer::show.modals.delete.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {!! trans('log-viewer::show.modals.delete.confirm', ['file' => $log->date]) !!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary mr-auto" data-dismiss="modal">{{ trans('log-viewer::show.modals.delete.cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Loading&hellip;">{{ trans('log-viewer::show.modals.delete.submit') }}</button>
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
                            alert('{!! trans('log-viewer::show.modals.delete.error') !!}');
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        alert('{!! trans('log-viewer::show.modals.delete.error') !!}');
                        console.error(errorThrown);
                        submitBtn.button('reset');
                    }
                });

                return false;
            });

            @unless (empty(log_styler()->toHighlight()))
            $('.stack-content').each(function() {
                var $this = $(this);
                var html = $this.html().trim()
                    .replace(/({!! join(log_styler()->toHighlight(), '|') !!})/gm, '<strong>$1</strong>');

                $this.html(html);
            });
            @endunless
        });
    </script>
@endsection
