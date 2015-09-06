@extends('log-viewer::_template.master')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-2 sidebar">
                @include('log-viewer::menu')
            </div>
            <div class="col-sm-10 main">
                <div class="form-group">
                    <input type="text" id="log-viewer-dates" class="form-control" />
                </div>

                <h1 class="page-header">Dashboard</h1>

                <div class="row placeholders">
                    <div class="col-xs-6 col-sm-3 placeholder">
                        <h4>Label</h4>
                        <span class="text-muted">Something else</span>
                    </div>
                    <div class="col-xs-6 col-sm-3 placeholder">
                        <h4>Label</h4>
                        <span class="text-muted">Something else</span>
                    </div>
                    <div class="col-xs-6 col-sm-3 placeholder">
                        <h4>Label</h4>
                        <span class="text-muted">Something else</span>
                    </div>
                    <div class="col-xs-6 col-sm-3 placeholder">
                        <h4>Label</h4>
                        <span class="text-muted">Something else</span>
                    </div>
                </div>

                <h2 class="sub-header">Section title</h2>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Header</th>
                                <th>Header</th>
                                <th>Header</th>
                                <th>Header</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1,001</td>
                                <td>Lorem</td>
                                <td>ipsum</td>
                                <td>dolor</td>
                                <td>sit</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        $(function () {
            $('#log-viewer-dates').datetimepicker({
                format: 'YYYY-MM-DD'
            });
        });
    </script>
@stop
