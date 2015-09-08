@extends('log-viewer::_template.master')

@section('content')
    <h1 class="page-header">Log [{{ $log->date }}]</h1>

    <div class="row">
        <div class="col-md-2">
            @include('log-viewer::_partials.menu')
        </div>
        <div class="col-md-10">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Log info :

                    <div class="group-btns pull-right">
                        <a href="{{ route('log-viewer::logs.download', [$log->date]) }}" class="btn btn-xs btn-success">
                            <i class="fa fa-download"></i> DOWNLOAD
                        </a>
                        <a href="#delete-log-modal" class="btn btn-xs btn-danger" data-toggle="modal">
                            <i class="fa fa-trash-o"></i> DELETE
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <td>File path :</td>
                            <td colspan="5">{{ $log->getPath() }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Log entries : </td>
                            <td>
                                    <span class="label label-primary">
                                        {{ $entries->total() }}
                                    </span>
                            </td>
                            <td>Size :</td>
                            <td>
                                <span class="label label-primary">{{ $log->size() }}</span>
                            </td>
                            <td>Created at :</td>
                            <td>
                                <span class="label label-primary">{{ $log->createdAt() }}</span>
                            </td>
                            <td>Updated at :</td>
                            <td>
                                <span class="label label-primary">{{ $log->updatedAt() }}</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-condensed" id="entries">
                    <thead>
                    <tr>
                        <td colspan="4">{!! $entries->render() !!}</td>
                    </tr>
                    <tr>
                        <th>ENV</th>
                        <th style="width: 120px;">Level</th>
                        <th style="width: 65px;">Time</th>
                        <th>Header</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($entries as $key => $entry)
                        <tr>
                            <td>
                                <span class="label label-env">
                                    {{ $entry->env }}
                                </span>
                            </td>
                            <td>
                                <span class="level level-{{ $entry->level }}">
                                    {!! $entry->level() !!}
                                </span>
                            </td>
                            <td>
                                <span class="label label-default">
                                    {{ $entry->datetime->format('H:i:s') }}
                                </span>
                            </td>
                            <td>
                                <p>{{ $entry->header }}</p>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">{!! $entries->render() !!}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

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
                        <h4 class="modal-title">DELETE LOG FILE</h4>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to <span class="label label-danger">DELETE</span> this log file <span class="label label-primary">{{ $log->date }}</span> ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-default pull-left" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Loading&hellip;">DELETE FILE</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        $(function () {
            var deleteLogModal = $('div#delete-log-modal'),
                    deleteLogForm  = $('form#delete-log-form'),
                    submitBtn      = deleteLogForm.find('button[type=submit]');

            deleteLogForm.submit(function(event) {
                event.preventDefault();
                submitBtn.button('loading');

                $.ajax({
                    url:      $(this).attr('action'),
                    type:     $(this).attr('method'),
                    dataType: 'json',
                    data:     $(this).serialize(),
                    success: function(data, textStatus, xhr) {
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
                        alert('AJAX ERROR ! Check the console !')
                        console.error(errorThrown);
                        submitBtn.button('reset');
                    }
                });

                return false;
            });
        });
    </script>
@stop
