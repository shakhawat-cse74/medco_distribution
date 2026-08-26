@extends('backend.layout.main')

@push('css')
    @include('backend.layout.partials.datatable_css')
@endpush
 @section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.field_payments')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.field_payments')}}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.field_payments')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="delivery_man_filter">{{__('db.filter_by_delivery_man')}}</label>
                            <select class="form-control" id="delivery_man_filter">
                                <option value="">{{__('db.all')}}</option>
                                @foreach($lims_delivery_man_list as $dm)
                                    <option value="{{ $dm->id }}">{{ $dm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped" id="fieldPaymentTable">
                            <thead>
                                <tr>
                                    <th>{{__('db.reference')}}</th>
                                    <th>{{__('db.field_order')}}</th>
                                    <th>{{__('db.delivery_man')}}</th>
                                    <th>{{__('db.customer')}}</th>
                                    <th>{{__('db.payment_method')}}</th>
                                    <th>{{__('db.amount')}}</th>
                                    <th>{{__('db.date')}}</th>
                                    <th>{{__('db.status')}}</th>
                                    <th class="not-exported">{{__('db.actions')}}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script type="text/javascript">
    $('#delivery_man_filter').on('change', function() {
        var deliveryManId = $(this).val();
        $('#fieldPaymentTable').DataTable().ajax.reload();
    });

    $(document).on('click', '.confirm-delete-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var paymentName = $btn.data('name');
        Swal.fire({
            title: '{{__("db.Are you sure")}}',
            text: '{{__("db.you_will_not_be able to revert this")}}',
            icon: 'warning',
            showConfirmButton: true,
            showCancelButtons: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d0',
            confirmButtonText: '{{__("db.yes_delete_it")}}',
            cancelButtonText: '{{__("db.cancel")}}'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.closest('form').submit();
            }
        });
    });

    $('#fieldPaymentTable').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"{{ route('field-payments.fieldPaymentData') }}",
            dataType: "json",
            type:"post",
            data: function (d) {
                d.delivery_man_id = $('#delivery_man_filter').val();
            }
        },
        "columns": [
            {"data": "reference_no"},
            {"data": "field_order"},
            {"data": "delivery_man"},
            {"data": "customer"},
            {"data": "payment_method"},
            {"data": "amount"},
            {"data": "date"},
            {"data": "status"},
            {"data": "options"},
        ],
        'language': {
            'lengthMenu': '_MENU_ {{__("db.records per page")}}',
            "info": '<small>{{__("db.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search": '{{__("db.Search")}}',
            'paginate': {
                'previous': '<i class="ti ti-chevron-left"></i>',
                'next': '<i class="ti ti-chevron-right"></i>'
            }
        },
        order:[['5', 'desc']],
        'columnDefs': [
            {
                "orderable": false,
                'targets': [8]
            },
        ],
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
                footer:true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="ti ti-file-type-xls"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="ti ti-file-type-csv"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<i title="print" class="ti ti-printer"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="ti ti-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    } );

</script>
@endpush
