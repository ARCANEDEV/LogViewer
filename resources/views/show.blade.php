@extends('log-viewer::_template.master')

@section('content')
    <h1 class="page-header">Log [{{ $log->date }}]</h1>

    <div class="row">
        <div class="col-md-2">
            @include('log-viewer::_partials.menu')
        </div>
        <div class="col-md-10">
            <div class="panel panel-default">
                <div class="panel-heading">Log info :</div>
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
                            <td colspan="3">{!! $entries->render() !!}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop

@section('scripts')
@stop
