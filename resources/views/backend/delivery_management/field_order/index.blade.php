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
                <h3 class="page-title">{{__('db.Field Orders')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.delivery_management')}}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-b-0">{{__('db.Field Orders')}}</h5>
                    <a href="{{ route('field-orders.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> {{__('db.Add Field Order')}}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="fieldOrderTable" class="table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th class="not-exported"></th>
                                    <th>{{__('db.Reference No')}}</th>
                                    <th>{{__('db.Delivery Man')}}</th>
                                    <th>{{__('db.Customer')}}</th>
                                    <th>{{__('db.Warehouse')}}</th>
                                    <th>{{__('db.Status')}}</th>
                                    <th>{{__('db.Grand Total')}}</th>
                                    <th>{{__('db.Order Type')}}</th>
                                    <th class="not-exported">{{__('db.action')}}</th>
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
    $("ul#delivery").siblings('a').attr('aria-expanded','true');
    $("ul#delivery").addClass("show");
    $("ul#delivery #field-orders-menu").addClass("active");

    var field_order_id = [];
    var user_verified = <?php echo json_encode(config('app.user_verified'))?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '.confirm-delete-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var fieldOrderName = $btn.data('name');
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

    $('#fieldOrderTable').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"{{ route('field-orders.fieldOrderData') }}",
            dataType: "json",
            type:"post"
        },
        "createdRow": function( row, data, dataIndex ) {
            $(row).attr('data-id', data['id']);
        },
        "columns": [
            {"data": "key"},
            {"data": "reference_no"},
            {"data": "delivery_man"},
            {"data": "customer"},
            {"data": "warehouse"},
            {"data": "status"},
            {"data": "grand_total"},
            {"data": "order_type"},
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
        order:[['1', 'asc']],
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 8]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
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
        'select': { style: 'multi',  selector: 'td:first-child'},
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
