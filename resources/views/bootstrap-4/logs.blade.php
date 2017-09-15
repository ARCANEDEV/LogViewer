@extends('log-viewer::_template.master')

@section('content')
    <h1 class="mb-4">Logs</h1>

    {!! $rows->render('log-viewer::_pagination.bootstrap-4') !!}

    <table class="table table-sm table-responsive table-hover">
        <thead>
            <tr>
                @foreach($headers as $key => $header)
                <th class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                    @if ($key == 'date')
                        <span class="badge badge-info level">{{ $header }}</span>
                    @else
                        <span class="badge level level-{{ $key }}">
                            {!! log_styler()->icon($key) . ' ' . $header !!}
                        </span>
                    @endif
                </th>
                @endforeach
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if ($rows->count() > 0)
                @foreach($rows as $date => $row)
                <tr>
                    @foreach($row as $key => $value)
                        <td class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                            @if ($key == 'date')
                                <a href="{{ route('log-viewer::logs.show', [$value]) }}" class="btn btn-sm btn-primary">
                                    {{ $value }}
                                </a>
                            @elseif ($value == 0)
                                <span class="badge level level-empty">{{ $value }}</span>
                            @else
                                <a href="{{ route('log-viewer::logs.filter', [$date, $key]) }}">
                                    <span class="badge level level-{{ $key }}">{{ $value }}</span>
                                </a>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-right">
                        <a href="{{ route('log-viewer::logs.show', [$date]) }}" class="btn btn-sm btn-info">
                            <i class="fa fa-search"></i>
                        </a>
                        <a href="{{ route('log-viewer::logs.download', [$date]) }}" class="btn btn-sm btn-success">
                            <i class="fa fa-download"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#delete-log-modal" data-log-date="{{ $date }}">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="11" class="text-center">
                        <span class="badge badge-default">{{ trans('log-viewer::general.empty-logs') }}</span>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    {!! $rows->render('log-viewer::_pagination.bootstrap-4') !!}

@endsection

@section('modals')
    {{-- DELETE MODAL --}}
    <div id="delete-log-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="delete-log-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="date" value="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Delete Log File</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p></p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Loading&hellip;"><i class="fa fa-trash-o"></i> DELETE FILE</button>
                        <button type="button" class="btn btn-sm btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {

            var deleteLogModal = $('#delete-log-modal'),
                deleteLogForm  = $('#delete-log-form'),
                submitBtn      = deleteLogForm.find('button[type=submit]');

            deleteLogModal.on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget) // Button that triggered the modal
                var logdate = button.data('log-date') // Extract info from data-* attributes
                var modal = $(this)
                modal.find('.modal-body p').html(
                    'Are you sure you want to <span class="badge badge-danger">DELETE</span> this log file <span class="badge badge-primary">' + logdate + '</span> ?'
                );
                deleteLogForm.find('input[name=date]').val(logdate)
            })

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
                            location.reload();
                        }
                        else {
                            alert('AJAX ERROR ! Check the console !');
                            console.error(data);
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

            deleteLogModal.on('hidden.bs.modal', function() {
                deleteLogForm.find('input[name=date]').val('');
                deleteLogModal.find('.modal-body p').html('');
            });
        });
    </script>
@endsection
