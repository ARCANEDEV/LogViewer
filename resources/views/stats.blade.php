@extends('log-viewer::_template.master')

@section('content')
    <h1 class="page-header">Dashboard</h1>

    @include('log-viewer::_partials.reports', compact('percents', 'reports'))

    <h2 class="sub-header">Logs</h2>

    <div class="table-responsive">
        <table class="table table-condensed table-hover table-stats">
            <thead>
                <tr>
                    @foreach($headers as $key => $header)
                        <th class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                            @if ($key == 'date')
                                <span class="label label-info">{{ $header }}</span>
                            @else
                                <span class="level level-{{ $key }}">
                                    {!! log_lvl_icon($key) . ' ' . $header !!}
                                </span>
                            @endif
                        </th>
                    @endforeach
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $date => $row)
                    <tr>
                        @foreach($row as $key => $value)
                            <td class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                @if ($key == 'date')
                                    <span class="label label-primary">{{ $value }}</span>
                                @else
                                    <span class="level level-{{ $value !== 0 ? $key : 'empty' }}">
                                        {{ $value }}
                                    </span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-right">
                            <a href="{{ route('log-viewer::log.show', [$date]) }}" class="btn btn-xs btn-info">
                                <i class="fa fa-search"></i>
                            </a>
                            <a href="{{ route('log-viewer::log.download', [$date]) }}" class="btn btn-xs btn-success">
                                <i class="fa fa-download"></i>
                            </a>
                            <a href="#delete-log-modal" class="btn btn-xs btn-danger" data-log-date="$date">
                                <i class="fa fa-trash-o"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                </tr>
            </tfoot>
        </table>
    </div>
@stop

@section('scripts')
@stop
