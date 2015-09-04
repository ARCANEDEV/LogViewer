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
                    <table class="table table-condensed table-hover">
                        <thead>
                            <tr>
                                @foreach($headers as $key => $header)
                                    <th class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                        {{ $header }}
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
                                            {{ $value }}
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
                                <th>Total</th>
                                @foreach($footer as $key => $value)
                                    <th class="text-center">{{ $value }}</th>
                                @endforeach
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
@stop