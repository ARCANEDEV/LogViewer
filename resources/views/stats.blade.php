@extends('log-viewer::_template.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-2 sidebar">
                @include('log-viewer::menu')
            </div>

            <div class="col-sm-10 main">
                <h1 class="page-header">Stats</h1>

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
                                        <a href="#" class="btn btn-xs btn-info">
                                            <i class="fa fa-search"></i>
                                        </a>
                                        <a href="#" class="btn btn-xs btn-danger">
                                            <i class="fa fa-trash-o"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>
                                    <span class="label label-info">Total :</span>
                                </th>
                                @foreach($footer as $key => $value)
                                    <th class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                        <span class="level level-{{  $key }}">{{ $value }}</span>
                                    </th>
                                @endforeach
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <h2 class="sub-header">Chart</h2>

                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <canvas id="stats-doughnut-chart"></canvas>
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <canvas id="stats-polar-area-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        $(function() {
            var data = {!! $statsJson !!};

            new Chart($('#stats-doughnut-chart')[0].getContext('2d'))
                .Doughnut(data, {
                    animationEasing : "easeOutQuart",
                });

            // new Chart($('#stats-polar-area-chart')[0].getContext('2d')).PolarArea(data);
        });
    </script>
@stop
