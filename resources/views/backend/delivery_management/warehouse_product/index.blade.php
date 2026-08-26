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
                <h3 class="page-title">{{__('db.Warehouse Products')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.Delivery Management')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Warehouse Products')}}</h5>
                    <a href="{{ route('warehouse-products.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> {{__('db.Add Warehouse Product')}}
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="warehouse_filter">{{__('db.filter_by_warehouse')}}</label>
                            <select class="form-control" id="warehouse_filter">
                                <option value="">{{__('db.all')}}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="warehouseProductTable" class="table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th class="not-exported"></th>
                                    <th>{{__('db.Warehouse')}}</th>
                                    <th>{{__('db.Product')}}</th>
                                    <th>{{__('db.Qty')}}</th>
                                    <th>{{__('db.Price')}}</th>
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

<!-- Edit Warehouse Product Modal -->
<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('warehouse-products.update', 1) }}" method="POST" id="editWarehouseProductForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 id="editModalLabel" class="modal-title">{{__('db.edit')}} {{__('db.Warehouse Product')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Warehouse')}} <span class="text-danger">*</span></label>
                            <select class="form-control" name="warehouse_id" id="edit_warehouse_id" required>
                                <option value="">{{__('db.Select Warehouse')}}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Product')}} <span class="text-danger">*</span></label>
                            <select class="form-control" name="product_id" id="edit_product_id" required>
                                <option value="">{{__('db.Select Product')}}</option>
                                @foreach($lims_product_list as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Qty')}} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="qty" id="edit_qty" required min="0" step="0.01">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Price')}} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price" id="edit_price" required min="0" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('db.close')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('db.update')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script type="text/javascript">
    $("ul#delivery").siblings('a').attr('aria-expanded','true');
    $("ul#delivery").addClass("show");
    $("ul#delivery #warehouse-products-menu").addClass("active");

    var warehouse_product_id = [];
    var user_verified = <?php echo json_encode(config('app.user_verified'))?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#warehouse_filter').on('change', function() {
        var warehouseId = $(this).val();
        $('#warehouseProductTable').DataTable().ajax.reload();
    });

    $(document).on('click', '.confirm-delete-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var productName = $btn.data('name');
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

    $('#warehouseProductTable').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"{{ route('warehouse-products.warehouseProductData') }}",
            dataType: "json",
            type:"post",
            data: function (d) {
                d.warehouse_id = $('#warehouse_filter').val();
            }
        },
        "createdRow": function( row, data, dataIndex ) {
            $(row).attr('data-id', data['id']);
        },
        "columns": [
            {"data": "key"},
            {"data": "warehouse"},
            {"data": "product"},
            {"data": "qty"},
            {"data": "price"},
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
                'targets': [0, 5]
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
                text: '<i title="delete" class="ti ti-x"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        warehouse_product_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                warehouse_product_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(warehouse_product_id.length && confirm("Are you sure want to delete selected warehouse products?")) {
                            $.ajax({
                                type:'POST',
                                url:'{{ route("warehouse-products.deletebyselection") }}',
                                data:{
                                    warehouseProductIdArray: warehouse_product_id
                                },
                                success:function(data){
                                    dt.rows({ page: 'current', selected: true }).deselect();
                                    dt.rows({ page: 'current', selected: true }).remove().draw(false);
                                }
                            });
                        }
                        else if(!warehouse_product_id.length)
                            alert('No warehouse product is selected!');
                    }
                    else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="ti ti-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    } );

    $(document).on("click", ".open-EditCategoryDialog", function() {
        var id = $(this).data('id').toString();
        $.get('warehouse-products/' + id + '/edit', function(data) {
            $('#edit_id').val(data.lims_warehouse_product.id);
            $('#edit_warehouse_id').val(data.lims_warehouse_product.warehouse_id);
            $('#edit_product_id').val(data.lims_warehouse_product.product_id);
            $('#edit_qty').val(data.lims_warehouse_product.qty);
            $('#edit_price').val(data.lims_warehouse_product.price);
            $('#editWarehouseProductForm').attr('action', 'warehouse-products/update/' + id);
        });
    });

</script>
@endpush
