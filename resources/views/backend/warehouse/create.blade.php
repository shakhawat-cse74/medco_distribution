@extends('backend.layout.main')
@push('css')
    @include('backend.layout.partials.datatable_css')
@endpush

@section('content')

<x-validation-error fieldName="name" />
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        @can('warehouse-add')
        <a href="#" data-toggle="modal" data-target="#addWarehouse" class="btn btn-info add-warehouse-btn"><i class="ti ti-plus"></i> {{__('db.Add Warehouse')}}</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table id="warehouse-table" class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>{{__('db.Warehouse')}}</th>
                    <th>{{__('db.Phone Number')}}</th>
                    <th>{{__('db.Email')}}</th>
                    <th>{{__('db.Address')}}</th>
                    <th>{{__('db.Status')}}</th>
                    <th>{{__('db.action')}}</th>
                </tr>
            </thead>
        </table>
    </div>
</section>

@include('backend.warehouse.add-warehouse')

<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
  <div role="document" class="modal-dialog modal-sm">
    <div class="modal-content">
        <form action="{{ route('warehouse.update', 1) }}" method="POST">
            @csrf
            @method('PUT')
      <div class="modal-header bg-light py-2">
        <h5 id="exampleModalLabel" class="modal-title"><i class="ti ti-edit mr-1"></i> {{__('db.Update Warehouse')}}</h5>
        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
      </div>
      <div class="modal-body p-3">
          <div class="form-group mb-2">
            <input type="hidden" name="warehouse_id">
            <label class="small mb-1">{{__('db.name')}} <span class="text-danger">*</span></label>
            <input type="text" placeholder="{{ __('db.Type WareHouse Name') }}" name="name" required="required" class="form-control form-control-sm">
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Phone Number')}} <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control form-control-sm" required>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Email')}}</label>
            <input type="email" name="email" placeholder="example@example.com" class="form-control form-control-sm">
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Address')}} <span class="text-danger">*</span></label>
            <textarea class="form-control form-control-sm" rows="2" name="address" required></textarea>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Status')}}</label>
            <select class="form-control form-control-sm" name="is_active">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="text-right">
            <button type="button" data-dismiss="modal" class="btn btn-sm btn-secondary">{{__('db.Cancel')}}</button>
            <button type="submit" class="btn btn-sm btn-primary">{{__('db.Update')}}</button>
          </div>
      </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script type="text/javascript">

    $("ul#setting").siblings('a').attr('aria-expanded','true');
    $("ul#setting").addClass("show");
    $("ul#setting #warehouse-menu").addClass("active");

    @if(config('database.connections.saleprosaas_landlord'))
        numberOfWarehouse = <?php echo json_encode($numberOfWarehouse)?>;
        $.ajax({
            type: 'GET',
            async: false,
            url: '{{route("package.fetchData", gen_setting()->package_id)}}',
            success: function(data) {
                if(data['number_of_warehouse'] > 0 && data['number_of_warehouse'] <= numberOfWarehouse) {
                    $("a.add-warehouse-btn").addClass('d-none');
                }
            }
        });
    @endif

    var user_verified = <?php echo json_encode(config('app.user_verified'))?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function confirmDelete() {
        if (confirm("Are you sure want to delete?")) {
            return true;
        }
        return false;
    }

    $(document).ready(function() {

        $(document).on('click', '.open-EditWarehouseDialog', function() {
            var url = "warehouse/"
            var id = $(this).data('id').toString();
            url = url.concat(id).concat("/edit");

            $.get(url, function(data) {
                $("#editModal input[name='name']").val(data['name']);
                $("#editModal input[name='phone']").val(data['phone']);
                $("#editModal input[name='email']").val(data['email']);
                $("#editModal textarea[name='address']").val(data['address']);
                var statusSelect = $("#editModal select[name='is_active']");
                statusSelect.val(data['is_active'] ? 1 : 0);
                statusSelect.trigger('change');
                $("#editModal input[name='warehouse_id']").val(data['id']);
            });
        });

    });

    $('#warehouse-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('warehouse.data') }}"
        },
    
        columns: [
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function () {
                    return '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                }
            },
            {data: 'name', name: 'name'},
            {data: 'phone', name: 'phone'},
            {data: 'email', name: 'email'},
            {data: 'address', name: 'address'},
            {
                data: 'is_active',
                name: 'is_active',
                render: function(data) {
                    return '<span class="badge badge-' + (data ? 'success' : 'danger') + '">' + (data ? 'Active' : 'Inactive') + '</span>';
                }
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ],
    
        rowCallback: function(row, data) {
            $(row).attr('data-id', data.id);
        },
    
        order: [],
    
        language: {
            lengthMenu: '_MENU_ {{ __("db.records per page") }}',
            info: '<small>{{ __("db.Showing") }} _START_ - _END_ (_TOTAL_)</small>',
            search: '{{ __("db.Search") }}',
            paginate: {
                previous: '<i class="ti ti-chevron-left"></i>',
                next: '<i class="ti ti-chevron-right"></i>'
            }
        },
    
        columnDefs: [
            {
                orderable: false,
                targets: [0, 5]
            }
        ],
    
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
    
        lengthMenu: [
            [10,25,50,100],
            [10,25,50,100]
        ],
    
        dom: '<"row"lfB>rtip',
    
        buttons: [
            // keep your existing export buttons here
        ]
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $( "#select_all" ).on( "change", function() {
        if ($(this).is(':checked')) {
            $("tbody input[type='checkbox']").prop('checked', true);
        }
        else {
            $("tbody input[type='checkbox']").prop('checked', false);
        }
    });

    $("#export").on("click", function(e){
        e.preventDefault();
        var warehouse = [];
        $(':checkbox:checked').each(function(i){
        warehouse[i] = $(this).val();
        });
        $.ajax({
        type:'POST',
        url:'/exportwarehouse',
        data:{

                warehouseArray: warehouse
            },
        success:function(data){
            alert('Exported to CSV file successfully! Click Ok to download file');
            window.location.href = data;
        }
        });
    });
</script>
@endpush