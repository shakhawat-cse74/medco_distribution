@extends('backend.layout.main') @section('content')
@if(session()->has('not_permitted'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>

    <div class="container-fluid">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error}}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session()->has('message'))
        <div class="alert alert-{{session('type')}} alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session('message') }}</div>
        @endif
        {{-- <a href="{{route('reveiws.create')}}" class="btn btn-info parent_load"><i class="fa fa-plus"></i> {{__('Add Review')}}</a> --}}
    </div>

    <div class="table-responsive">
        <table id="review_list_table" class="table ">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th scope="col">{{__('db.Customer Name') }}</th>
                    <th scope="col">{{__('db.Product') }}</th>
                    <th scope="col">{{__('db.Review') }}</th>
                    <th scope="col">{{__('db.Rating') }}</th>
                    <th scope="col">{{__('db.status') }}</th>
                    <th scope="col">{{__('db.date') }}</th>
                    <th scope="col">{{__('db.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $key=>$review)
                <tr data-id="{{$review->id}}">
                    <td>{{$key}}</td>
                    <td>{{ @$review->customer_name ?? '--' }}</td>
                    <td>{{ @$review->product->name ?? '--' }}</td>
                    <td>{{ @$review->review ?? '--' }}</td>
                    <td>
                        @php
                            $rating = @$review->rating ?? 0;
                        @endphp

                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $rating)
                                <i class="fa fa-star text-warning"></i> {{-- Filled star --}}
                            @else
                                <i class="fa fa-star-o text-secondary"></i> {{-- Empty star --}}
                            @endif
                        @endfor
                    </td>

                    <td>
                        @if(isset($review->approved))
                            <button
                                class="btn btn-sm status-toggle-btn {{ $review->approved == 1 ? 'btn-success' : 'btn-warning' }}"
                                data-id="{{ $review->id }}"
                                data-status="{{ $review->approved }}"
                            >
                                {{ $review->approved == 1 ? 'Approved' : 'Pending' }}
                            </button>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </td>


                    <td>{{ Carbon\Carbon::parse($review->created_at)->format('Y-M-d')  }}</td>
                    <td>
                        <form action="{{ route('reviews.destroy', $review->id) }}" method="GET" class="d-inline">
                            <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure want to delete?')">
                                <i class="dripicons-trash"></i>
                            </button>&nbsp;&nbsp;
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<div id="confirmModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{{__('db.Confirmation')}}</h2>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h4 align="center" style="margin:0;">{{__('want to remove?')}}</h4>
            </div>
            <div class="modal-footer">
                <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">{{__('db.OK')}}'
                </button>
                <button type="button" class="close btn-default" data-dismiss="modal">{{__('db.Cancel')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
    "use strict";

    function confirmDelete() {
        if (confirm("Are you sure want to delete?")) {
            return true;
        }
        return false;
    }

    $('#review_list_table').DataTable({
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
            "info": '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search": '{{trans("file.Search")}}',
            'paginate': {
                'previous': '<i class="dripicons-chevron-left"></i>',
                'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [{
                "orderable": false,
                'targets': [0]
            },
            {
                'render': function(data, type, row, meta) {
                    if (type === 'display') {
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }

                    return data;
                },
                'checkboxes': {
                    'selectRow': true,
                    'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': {
            style: 'multi',
            selector: 'td:first-child'
        },
        'lengthMenu': [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        dom: '<"row"lfB>rtip',
        buttons: [{
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    stripHtml: false
                },
                customize: function(doc) {
                    for (var i = 1; i < doc.content[1].table.body.length; i++) {
                        if (doc.content[1].table.body[i][0].text.indexOf('<img src=') !== -1) {
                            var imagehtml = doc.content[1].table.body[i][0].text;
                            var regex = /<img.*?src=['"](.*?)['"]/;
                            var src = regex.exec(imagehtml)[1];
                            var tempImage = new Image();
                            tempImage.src = src;
                            var canvas = document.createElement("canvas");
                            canvas.width = tempImage.width;
                            canvas.height = tempImage.height;
                            var ctx = canvas.getContext("2d");
                            ctx.drawImage(tempImage, 0, 0);
                            var imagedata = canvas.toDataURL("image/png");
                            delete doc.content[1].table.body[i][0].text;
                            doc.content[1].table.body[i][0].image = imagedata;
                            doc.content[1].table.body[i][0].fit = [30, 30];
                        }
                    }
                },
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    format: {
                        body: function(data, row, column, node) {
                            if (column === 0 && (data.indexOf('<img src=') !== -1)) {
                                var regex = /<img.*?src=['"](.*?)['"]/;
                                data = regex.exec(data)[1];
                            }
                            return data;
                        }
                    }
                },
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    stripHtml: false
                },
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function(e, dt, node, config) {
                    if (user_verified == '1') {
                        brand_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                brand_id[i - 1] = $(this).closest('tr').data('id');
                            }
                        });
                        if (brand_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'brand/deletebyselection',
                                data: {
                                    brandIdArray: brand_id
                                },
                                success: function(data) {
                                    alert(data);
                                }
                            });
                            dt.rows({
                                page: 'current',
                                selected: true
                            }).remove().draw(false);
                        } else if (!brand_id.length)
                            alert('No brand is selected!');
                    } else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    });



        $('#page_list_table').on('click', '.status', function() {
            let id = $(this).data('id');
            let status = $(this).data('status');

            var target = "{{route('page.index')}}/" + id + '/' + status;

            $.ajax({
                url: target,
                dataType: "json",
                success: function(data) {
                    let html = '';
                    if (data.success) {
                        location.reload();
                    }
                }
            })
        });

        $('.status-toggle-btn').click(function() {
            var button = $(this);
            var id = button.data('id');
            var currentStatus = button.data('status');
            var actionText = currentStatus == 1 ? 'mark as Pending' : 'mark as Approved';

            if (confirm('Are you sure you want to ' + actionText + '?')) {
                $.ajax({
                    url: "{{ route('reviews.toggleStatus') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            button
                                .text(response.label)
                                .removeClass('btn-success btn-warning')
                                .addClass(response.btn_class)
                                .data('status', response.new_status);
                        } else {
                            alert(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                        alert('AJAX error: ' + xhr.statusText);
                    }
                });
            }
        });
    </script>

@endpush
