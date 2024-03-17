@extends('log-viewer::bootstrap-5._master')

<?php /** @var  Illuminate\Pagination\LengthAwarePaginator  $rows */ ?>

@section('content')
    <div class="page-header mb-4">
        <h1>@lang('Logs')</h1>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    @foreach($headers as $key => $header)
                    <th scope="col" class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                        @if ($key == 'date')
                            <span class="badge text-bg-info">{{ $header }}</span>
                        @else
                            <span class="badge badge-level-{{ $key }}">
                                {{ log_styler()->icon($key) }} {{ $header }}
                            </span>
                        @endif
                    </th>
                    @endforeach
                    <th scope="col" class="text-end">@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $date => $row)
                    <tr>
                        @foreach($row as $key => $value)
                            <td class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                @if ($key == 'date')
                                    <span class="badge text-bg-primary">{{ $value }}</span>
                                @elseif ($value == 0)
                                    <span class="badge empty">{{ $value }}</span>
                                @else
                                    <a href="{{ route('log-viewer::logs.filter', [$date, $key]) }}">
                                        <span class="badge badge-level-{{ $key }}">{{ $value }}</span>
                                    </a>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-end">
                            <a href="{{ route('log-viewer::logs.show', [$date]) }}" class="btn btn-sm btn-info">
                                <i class="fa fa-fw fa-search"></i>
                            </a>
                            <a href="{{ route('log-viewer::logs.download', [$date]) }}" class="btn btn-sm btn-success">
                                <i class="fa fa-fw fa-download"></i>
                            </a>
                            <a href="#delete-log-modal" class="btn btn-sm btn-danger" data-log-date="{{ $date }}">
                                <i class="fa fa-fw fa-trash-o"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">
                            <span class="badge text-bg-secondary">@lang('The list of logs is empty!')</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $rows->render() }}
@endsection

@section('modals')
    {{-- DELETE MODAL --}}
    <div class="modal fade" id="delete-log-modal" tabindex="-1" aria-labelledby="delete-log-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <input type="hidden" name="date" value="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="delete-log-modal-label">@lang('Delete log file')</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p></p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="@lang('Loading')&hellip;">@lang('Delete')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        ready(() => {
            let deleteLogModal = new bootstrap.Modal('div#delete-log-modal')
            let deleteLogModalElt = deleteLogModal._element
            let deleteLogForm = document.querySelector('form#delete-log-form')
            let submitBtn = new bootstrap.Button(deleteLogForm.querySelector('button[type=submit]'))

            document.querySelectorAll("a[href='#delete-log-modal']").forEach((elt) => {
                elt.addEventListener('click', (event) => {
                    event.preventDefault()

                    let date = event.currentTarget.getAttribute('data-log-date')
                    let message = "{{ __('Are you sure you want to delete this log file: :date ?') }}"

                    deleteLogForm.querySelector('input[name=date]').value = date
                    deleteLogModalElt.querySelector('.modal-body p').innerHTML = message.replace(':date', date)

                    deleteLogModal.show()
                })
            })

            deleteLogForm.addEventListener('submit', (event) => {
                event.preventDefault()
                submitBtn.toggle('loading')

                fetch(event.currentTarget.getAttribute('action'), {
                    method: 'DELETE',
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        'Content-type': 'application/json'
                    },
                    body: JSON.stringify({
                        date: event.currentTarget.querySelector("input[name='date']").value,
                    })
                })
                    .then((resp) => resp.json())
                    .then((resp) => {
                        if (resp.result === 'success') {
                            deleteLogModal.hide()
                            location.reload()
                        }
                        else {
                            alert('AJAX ERROR ! Check the console !')
                            console.error(resp)
                        }
                    })
                    .catch((err) => {
                        alert('AJAX ERROR ! Check the console !')
                        console.error(err)
                    })

                return false
            })

            deleteLogModalElt.addEventListener('hidden.bs.modal', () => {
                deleteLogForm.querySelector('input[name=date]').value = ''
                deleteLogModalElt.querySelector('.modal-body p').innerHTML = ''
            })
        })
    </script>
@endsection
