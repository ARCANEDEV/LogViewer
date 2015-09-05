@extends('log-viewer::_template.master')

@section('content')
    <h1 class="page-header">Log [{{ $log->date }}]</h1>
    <p>
        <span class="label label-default">{{ $log->getPath() }}</span>
    </p>

    <div class="row">
        <div class="col-md-2">
            <div class="panel panel-default">
                <div class="panel-heading">Levels</div>
                <ul class="list-group">
                    @foreach($log->menu() as $level => $item)
                        @if ($item['count'] == 0)
                            <a href="#" class="list-group-item disabled">
                                <span class="badge">{{ $item['count'] }}</span>
                                {{ $item['name'] }}
                            </a>
                        @else
                            <a href="{{ route('log-viewer::log.filter', [$log->date, $level]) }}" class="list-group-item {{ $level }}">
                                <span class="badge">{{ $item['count'] }}</span>
                                {{ $item['name'] }}
                            </a>
                        @endif
                    @endforeach
                </ul>
            </div>

        </div>
        <div class="col-md-10">
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