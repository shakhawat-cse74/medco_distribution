@extends('backend.layout.main')
@push('css')
    @include('backend.layout.partials.datatable_css')
@endpush
@section('content')
    <x-success-message key="message" />
    <x-error-message key="not_permitted" />
    <section>
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-12">
                            <h3 class="page-title">{{ __('db.Delivery List') }}</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i>
                                        Dashboard</a></li>
                                <li class="breadcrumb-item active">{{ __('db.delivery_management') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="page-block">
        @if(!isset($isDeliveryMan) || !$isDeliveryMan)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-b-0">{{ __('db.Assign Delivery') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('delivery-man-delivery.assign') }}" method="POST" id="assignDeliveryForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Reference No <span class="text-danger">*</span></label>
                                        <input type="text" name="field_order_id" class="form-control" required
                                            placeholder="Enter reference number">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Delivery Man <span class="text-danger">*</span></label>
                                        <select name="delivery_man_id" class="form-control selectpicker" data-live-search="true" required>
                                            <option value="">Select Delivery Man</option>
                                            @foreach ($lims_delivery_man_list as $dm)
                                                <option value="{{ $dm->id }}">{{ $dm->name }} - {{ $dm->phone_number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Priority</label>
                                        <select name="priority" class="form-control">
                                            <option value="normal">Normal</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="ti ti-check"></i> Assign
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="assignMessage" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-b-0">{{ __('db.Delivery List') }}</h5>
                        @if(!isset($isDeliveryMan) || !$isDeliveryMan)
                        <a href="{{ route('delivery-man-delivery.mapView') }}" class="btn btn-info btn-sm"><i
                                class="ti ti-map-2"></i> {{ __('db.Map View') }}</a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="deliveryTable" class="table" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th class="not-exported"></th>
                                        <th>{{ __('db.Reference') }}</th>
                                        <th>{{ __('db.Delivery Man') }}</th>
                                        <th>{{ __('db.customer') }}</th>
                                        <th>{{ __('db.Address') }}</th>
                                        <th>{{ __('db.status') }}</th>
                                        <th>{{ __('db.Priority') }}</th>
                                        <th class="not-exported">{{ __('db.action') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Delivery Modal -->
    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="editDeliveryLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <form id="editDeliveryForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 id="editDeliveryLabel" class="modal-title">{{ __('db.Update Delivery Status') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="ti ti-x"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('db.status') }} *</label>
                            <select name="status" class="form-control" required>
                                <option value="assigned">{{ __('db.Assigned') }}</option>
                                <option value="started">{{ __('db.Started') }}</option>
                                <option value="completed">{{ __('db.Completed') }}</option>
                                <option value="failed">{{ __('db.Failed') }}</option>
                                <option value="due">{{ __('db.Due') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('db.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('db.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
    <script type="text/javascript">
        $("ul#delivery").siblings('a').attr('aria-expanded', 'true');
        $("ul#delivery").addClass("show");
        $("ul#delivery #delivery-list-menu").addClass("active");

        $('#deliveryTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "{{ route('delivery-man-delivery.deliveryListData') }}",
                dataType: "json",
                type: "GET"
            },
            "createdRow": function(row, data, dataIndex) {
                $(row).attr('data-id', data['id']);
            },
            "columns": [
                {"data": "key"},
                {"data": "reference_no"},
                {"data": "delivery_man"},
                {"data": "customer"},
                {"data": "address"},
                {"data": "status"},
                {"data": "priority"},
                @if(!isset($isDeliveryMan) || !$isDeliveryMan)
                {"data": "options"},
                @endif
            ],
            'language': {
                'lengthMenu': '_MENU_ {{ __('db.records per page') }}',
                "info": '<small>{{ __('db.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ __('db.Search') }}',
                'paginate': {
                    'previous': '<i class="ti ti-chevron-left"></i>',
                    'next': '<i class="ti ti-chevron-right"></i>'
                }
            },
            order: [['1', 'desc']],
            'columnDefs': [
                @if(!isset($isDeliveryMan) || !$isDeliveryMan)
                {
                    "orderable": false,
                    'targets': [0, 7]
                },
                @else
                {
                    "orderable": false,
                    'targets': [0]
                },
                @endif
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
            'select': { style: 'multi', selector: 'td:first-child' },
            'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
            dom: '<"row"lfB>rtip',
            buttons: [
                {
                    extend: 'pdf',
                    text: '<i title="export to pdf" class="ti ti-file-type-pdf"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer: true
                },
                {
                    extend: 'excel',
                    text: '<i title="export to excel" class="ti ti-file-type-xls"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer: true
                },
                {
                    extend: 'csv',
                    text: '<i title="export to csv" class="ti ti-file-type-csv"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer: true
                },
                {
                    extend: 'print',
                    text: '<i title="print" class="ti ti-printer"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer: true
                },
                {
                    extend: 'colvis',
                    text: '<i title="column visibility" class="ti ti-eye"></i>',
                    columns: ':gt(0)'
                },
            ],
        });

        $(document).on("click", ".open-EditCategoryDialog", function() {
            var id = $(this).data('id').toString();
            $('#editDeliveryForm').attr('action', 'delivery-man-delivery/update-status/' + id);
            $('#editModal').modal('show');
        });

        $('#assignDeliveryForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var originalBtnText = $btn.html();
            $btn.prop('disabled', true).html('<i class="ti ti-loader-2"></i> Assigning...');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#assignMessage').html('<div class="alert alert-success"><i class="ti ti-check"></i> ' + response.message + '</div>');
                        $form[0].reset();
                        $('#deliveryTable').DataTable().ajax.reload();
                    } else {
                        $('#assignMessage').html('<div class="alert alert-danger"><i class="ti ti-x"></i> ' + response.message + '</div>');
                    }
                },
                error: function(xhr) {
                    var message = 'Assignment failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    $('#assignMessage').html('<div class="alert alert-danger"><i class="ti ti-x"></i> ' + message + '</div>');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalBtnText);
                }
            });
        });
    </script>
@endpush
