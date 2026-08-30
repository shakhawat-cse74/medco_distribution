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
                <h3 class="page-title">{{__('db.delivery_men_list')}}</h3>
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
                    <h5 class="m-b-0">{{__('db.delivery_men_list')}}</h5>
                    <a href="{{ route('delivery-men.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> {{__('db.add_delivery_man')}}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="deliveryManTable" class="table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th class="not-exported"></th>
                                    <th>{{__('db.name')}}</th>
                                    <th>{{__('db.Phone')}}</th>
                                    <th>{{__('db.Warehouse')}}</th>
                                    <th>{{__('db.status')}}</th>
                                    <th>{{__('db.Performance')}}</th>
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

<!-- Edit Delivery Man Modal -->
<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('delivery-men.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 id="editModalLabel" class="modal-title">{{__('db.edit')}} {{__('db.delivery_men_list')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{__('db.name')}} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Phone')}} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone_number" id="edit_phone_number" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Email')}}</label>
                            <input type="email" class="form-control" name="email" id="edit_email">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Password')}}</label>
                            <input type="password" class="form-control" name="password">
                            <small class="text-muted">{{__('db.Leave blank to keep current')}}</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Address')}}</label>
                            <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.City')}}</label>
                            <input type="text" class="form-control" name="city" id="edit_city">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Country')}}</label>
                            <input type="text" class="form-control" name="country" id="edit_country">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.NID Number')}}</label>
                            <input type="text" class="form-control" name="nid_number" id="edit_nid_number">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.License Number')}}</label>
                            <input type="text" class="form-control" name="license_number" id="edit_license_number">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Vehicle Type')}}</label>
                            <select class="form-control" name="vehicle_type" id="edit_vehicle_type">
                                <option value="">{{__('db.Select Vehicle Type')}}</option>
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="Car">Car</option>
                                <option value="Van">Van</option>
                                <option value="Truck">Truck</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Vehicle Number')}}</label>
                            <input type="text" class="form-control" name="vehicle_number" id="edit_vehicle_number">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{__('db.Warehouse')}}</label>
                            <select class="form-control" name="warehouse_id" id="edit_warehouse_id">
                                <option value="">{{__('db.Select Warehouse')}}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>{{__('db.Note')}}</label>
                            <textarea class="form-control" name="note" id="edit_note" rows="2"></textarea>
                        </div>
                        <div class="col-md-12 form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                                <label class="custom-control-label" for="edit_is_active">{{__('db.Active')}}</label>
                            </div>
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
    $("ul#delivery #delivery-men-list-menu").addClass("active");

    var delivery_man_id = [];
    var user_verified = <?php echo json_encode(config('app.user_verified'))?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on("click", ".open-EditCategoryDialog", function() {
        var id = $(this).data('id').toString();
        $.get('delivery-men/' + id + '/edit', function(data) {
            $('#edit_id').val(data.id);
            $('#edit_name').val(data.name);
            $('#edit_email').val(data.email);
            $('#edit_phone_number').val(data.phone_number);
            $('#edit_address').val(data.address);
            $('#edit_city').val(data.city);
            $('#edit_country').val(data.country);
            $('#edit_nid_number').val(data.nid_number);
            $('#edit_license_number').val(data.license_number);
            $('#edit_vehicle_type').val(data.vehicle_type);
            $('#edit_vehicle_number').val(data.vehicle_number);
            $('#edit_warehouse_id').val(data.warehouse_id);
            $('#edit_user_id').val(data.user_id);
            $('#edit_note').val(data.note);
            $('#edit_is_active').prop('checked', data.is_active == true);
        });
    });

    $(document).on("click", ".toggle-status", function() {
        var id = $(this).data('id').toString();
        if (confirm("{{__('db.Are you sure you want to toggle status?')}}")) {
            $.post('delivery-men/toggle-status', { id: id }, function() {
                location.reload();
            });
        }
    });

    $(document).on('click', '.confirm-delete-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var deliveryManName = $btn.data('name');
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

    $('#deliveryManTable').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"{{ route('delivery-men.deliveryManData') }}",
            dataType: "json",
            type:"post"
        },
        "createdRow": function( row, data, dataIndex ) {
            $(row).attr('data-id', data['id']);
        },
        "columns": [
            {"data": "key"},
            {"data": "name"},
            {"data": "phone_number"},
            {"data": "warehouse_id"},
            {"data": "is_active"},
            {"data": "performance"},
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
                'targets': [0, 6]
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
                        delivery_man_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                delivery_man_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(delivery_man_id.length && confirm("If you delete delivery man, all related data will be removed. Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'delivery-men/deletebyselection',
                                data:{
                                    deliveryManIdArray: delivery_man_id
                                },
                                success:function(data){
                                    dt.rows({ page: 'current', selected: true }).deselect();
                                    dt.rows({ page: 'current', selected: true }).remove().draw(false);
                                }
                            });
                        }
                        else if(!delivery_man_id.length)
                            alert('No delivery man is selected!');
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

</script>
@endpush
