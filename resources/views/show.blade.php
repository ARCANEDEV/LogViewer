@extends('log-viewer::_template.master')

@section('content')
    <h1 class="page-header">Log [{{ $log->date }}]</h1>

    <div class="row">
        <div class="col-md-2">
            @include('log-viewer::_partials.menu', ['menu' => $log->menu(), 'date' => $log->date])
        </div>
        <div class="col-md-10">
            <div class="table-responsive">
                <table class="table table-condensed">
                    <thead>
                        <tr>
                            <td>File path :</td>
                            <td colspan="5">
                                {{ $log->getPath() }}
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Size :</td>
                            <td>{{ $log->size() }}</td>
                            <td>Create at :</td>
                            <td>{{ $log->createdAt() }}</td>
                            <td>Updated at :</td>
                            <td>{{ $log->updatedAt() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <div class="table-responsive">
                <table class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th style="width: 120px;">Level</th>
                        <th style="width: 65px;">Time</th>
                        <th>Header</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td>{{ $entry->level }}</td>
                            <td>
                        <span class="label label-default">
                            {{ $entry->datetime->format('H:i:s') }}
                        </span>
                            </td>
                            <td>{{ $entry->header }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop

@section('scripts')
@stop