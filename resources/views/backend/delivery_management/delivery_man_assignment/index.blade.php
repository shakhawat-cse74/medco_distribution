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
                <h3 class="page-title">{{__('db.Route Assignments')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('delivery-man.dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.Route Assignments')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Assignments List')}}</h5>
                    @can('delivery-man-assignments-add')
                    <a href="#" data-toggle="modal" data-target="#createModal" class="btn btn-info btn-sm">
                        <i class="ti ti-plus"></i> {{__('db.Add New Assignment')}}
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="assignmentTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{{__('db.Delivery Man')}}</th>
                                    <th>{{__('db.Warehouse')}}</th>
                                    <th>{{__('db.Route')}}</th>
                                    <th>{{__('db.Status')}}</th>
                                    <th>{{__('db.action')}}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true" class="modal fade text-left">
  <div role="document" class="modal-dialog modal-sm">
    <div class="modal-content">
        <form action="{{ route('delivery-man-assignments.store') }}" method="POST">
            @csrf
      <div class="modal-header bg-light py-2">
        <h5 id="createModalLabel" class="modal-title"><i class="ti ti-user-plus mr-1"></i> {{__('db.Add New Assignment')}}</h5>
        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
      </div>
      <div class="modal-body p-3">
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Delivery Man')}} <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm" name="delivery_man_id" required>
                <option value="">{{__('db.Select Delivery Man')}}</option>
                @foreach($lims_delivery_man_list as $man)
                    <option value="{{ $man->id }}">{{ $man->name }}</option>
                @endforeach
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Warehouse')}} <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm" name="warehouse_id" id="create_warehouse_id" required>
                <option value="">{{__('db.Select Warehouse')}}</option>
                @foreach($lims_warehouse_list as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Route')}}</label>
            <select class="form-control form-control-sm" name="route_id" id="create_route_id">
                <option value="">{{__('db.Select Route')}}</option>
                @foreach($lims_route_list as $route)
                    <option value="{{ $route->id }}" data-warehouse="{{ $route->warehouse_id }}">{{ $route->name }} ({{ $route->city ?? 'N/A' }})</option>
                @endforeach
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Status')}}</label>
            <select class="form-control form-control-sm" name="is_primary">
              <option value="0">No</option>
              <option value="1">Yes</option>
            </select>
          </div>
          <div class="text-right">
            <button type="button" data-dismiss="modal" class="btn btn-sm btn-secondary">{{__('db.Cancel')}}</button>
            <button type="submit" class="btn btn-sm btn-primary">{{__('db.Save')}}</button>
          </div>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true" class="modal fade text-left">
  <div role="document" class="modal-dialog modal-sm">
    <div class="modal-content">
        <form action="{{ route('delivery-man-assignments.update', 1) }}" method="POST">
            @csrf
            @method('PUT')
      <div class="modal-header bg-light py-2">
        <h5 id="editModalLabel" class="modal-title"><i class="ti ti-edit mr-1"></i> {{__('db.Edit Assignment')}}</h5>
        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
      </div>
      <div class="modal-body p-3">
          <div class="form-group mb-2">
            <input type="hidden" name="assignment_id" id="edit_assignment_id">
            <label class="small mb-1">{{__('db.Delivery Man')}} <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm" name="delivery_man_id" id="edit_delivery_man_id" required>
                <option value="">{{__('db.Select Delivery Man')}}</option>
                @foreach($lims_delivery_man_list as $man)
                    <option value="{{ $man->id }}">{{ $man->name }}</option>
                @endforeach
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Warehouse')}} <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm" name="warehouse_id" id="edit_warehouse_id" required>
                <option value="">{{__('db.Select Warehouse')}}</option>
                @foreach($lims_warehouse_list as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Route')}}</label>
            <select class="form-control form-control-sm" name="route_id" id="edit_route_id">
                <option value="">{{__('db.Select Route')}}</option>
                @foreach($lims_route_list as $route)
                    <option value="{{ $route->id }}" data-warehouse="{{ $route->warehouse_id }}">{{ $route->name }} ({{ $route->city ?? 'N/A' }})</option>
                @endforeach
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Status')}}</label>
            <select class="form-control form-control-sm" name="is_primary" id="edit_is_primary">
              <option value="0">No</option>
              <option value="1">Yes</option>
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
        $(document).ready(function() {
            $("ul#delivery-management").siblings('a').attr('aria-expanded','true');
            $("ul#delivery-management").addClass("show");
            $("ul#delivery-management #delivery-man-assignments-menu").addClass("active");

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).on('click', '.open-EditAssignmentDialog', function() {
                var id = $(this).data('id');
                $.get("{{ url('delivery-man-assignments') }}/" + id + "/edit", function(data) {
                    $("#edit_assignment_id").val(data.id);
                    $("#edit_delivery_man_id").val(data.delivery_man_id);
                    $("#edit_warehouse_id").val(data.warehouse_id);
                    $("#edit_route_id").val(data.route_id);
                    $("#edit_is_primary").val(data.is_primary ? 1 : 0);
                    $("#editModal form").attr('action', "{{ url('delivery-man-assignments') }}/" + data.id + "/update");
                });
            });

            $('#assignmentTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('delivery-man-assignments.data') }}",
                    dataType: "json",
                    type:"post"
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
                    {data: 'delivery_man_name', name: 'delivery_man_name'},
                    {data: 'warehouse_name', name: 'warehouse_name'},
                    {data: 'route_name', name: 'route_name'},
                    {
                        data: 'is_primary',
                        name: 'is_primary',
                        render: function(data) {
                            return '<span class="badge badge-' + (data ? 'success' : 'warning') + '">' + (data ? 'Primary' : 'Secondary') + '</span>';
                        }
                    },
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
                    },
                    {
                        extend: 'excel',
                        text: '<i title="export to excel" class="ti ti-file-type-xls"></i>',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        },
                    },
                    {
                        extend: 'csv',
                        text: '<i title="export to csv" class="ti ti-file-text"></i>',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        },
                    },
                    {
                        extend: 'print',
                        text: '<i title="print" class="ti ti-printer"></i>',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        },
                        customize: function(win) {
                            $(win.document.body).css('font-size', '10px');
                            $(win.document.body).css('direction', '{{config("app.locale") == "ar" ? "rtl" : "ltr"}}');
                            $(win.document.body).find('table').addClass('table table-bordered');
                            $(win.document.body).find('th').addClass('bg-info');
                            $(win.document.body).find('table').css('font-size', 'inherit');
                            $(win.document.body).find('table').css('border', '1px solid #ddd');
                        }
                    }
                ],
            });
        });
    </script>
@endpush
