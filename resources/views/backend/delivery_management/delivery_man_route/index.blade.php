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
                <h3 class="page-title">{{__('db.Routes')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('delivery-man.dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.Routes')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Routes List')}}</h5>
                    @can('delivery-man-routes-add')
                    <a href="#" data-toggle="modal" data-target="#createModal" class="btn btn-info btn-sm">
                        <i class="ti ti-plus"></i> {{__('db.Add New Route')}}
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="routeTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{{__('db.Route Name')}}</th>
                                    <th>{{__('db.City')}}</th>
                                    <th>{{__('db.Zone')}}</th>
                                    <th>{{__('db.Delivery Charge')}}</th>
                                    <th>{{__('db.Estimated Days')}}</th>
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
        <form action="{{ route('delivery-man-routes.store') }}" method="POST">
            @csrf
      <div class="modal-header bg-light py-2">
        <h5 id="createModalLabel" class="modal-title"><i class="ti ti-map-plus mr-1"></i> {{__('db.Add New Route')}}</h5>
        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
      </div>
      <div class="modal-body p-3">
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Route Name')}} <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" name="name" required>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.City')}}</label>
            <input type="text" class="form-control form-control-sm" name="city">
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Zone')}}</label>
            <input type="text" class="form-control form-control-sm" name="zone">
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Delivery Charge')}} <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm" name="delivery_charge" required>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Estimated Days')}} <span class="text-danger">*</span></label>
            <input type="number" class="form-control form-control-sm" name="estimated_days" required>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Status')}}</label>
            <select class="form-control form-control-sm" name="is_active">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Note')}}</label>
            <textarea class="form-control form-control-sm" rows="2" name="note"></textarea>
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
        <form action="{{ route('delivery-man-routes.update', 1) }}" method="POST">
            @csrf
            @method('PUT')
      <div class="modal-header bg-light py-2">
        <h5 id="editModalLabel" class="modal-title"><i class="ti ti-edit mr-1"></i> {{__('db.Edit Route')}}</h5>
        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
      </div>
      <div class="modal-body p-3">
          <div class="form-group mb-2">
            <input type="hidden" name="route_id" id="edit_route_id">
            <label class="small mb-1">{{__('db.Route Name')}} <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" name="name" id="edit_name" required>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.City')}}</label>
            <input type="text" class="form-control form-control-sm" name="city" id="edit_city">
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Zone')}}</label>
            <input type="text" class="form-control form-control-sm" name="zone" id="edit_zone">
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Delivery Charge')}} <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm" name="delivery_charge" id="edit_delivery_charge" required>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Estimated Days')}} <span class="text-danger">*</span></label>
            <input type="number" class="form-control form-control-sm" name="estimated_days" id="edit_estimated_days" required>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Status')}}</label>
            <select class="form-control form-control-sm" name="is_active" id="edit_is_active">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="small mb-1">{{__('db.Note')}}</label>
            <textarea class="form-control form-control-sm" rows="2" name="note" id="edit_note"></textarea>
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
            $("ul#delivery-management #delivery-man-routes-menu").addClass("active");

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).on('click', '.open-EditRouteDialog', function() {
                var id = $(this).data('id');
                $.get("{{ url('delivery-man-routes') }}/" + id + "/edit", function(data) {
                    $("#edit_route_id").val(data.id);
                    $("#edit_name").val(data.name);
                    $("#edit_city").val(data.city);
                    $("#edit_zone").val(data.zone);
                    $("#edit_delivery_charge").val(data.delivery_charge);
                    $("#edit_estimated_days").val(data.estimated_days);
                    $("#edit_is_active").val(data.is_active ? 1 : 0);
                    $("#edit_note").val(data.note);
                    $("#editModal form").attr('action', "{{ url('delivery-man-routes') }}/" + data.id + "/update");
                });
            });

            $('#routeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('delivery-man-routes.data') }}",
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
                    {data: 'name', name: 'name'},
                    {data: 'city', name: 'city'},
                    {data: 'zone', name: 'zone'},
                    {data: 'delivery_charge', name: 'delivery_charge'},
                    {data: 'estimated_days', name: 'estimated_days'},
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data) {
                            return '<span class="badge badge-' + (data ? 'success' : 'danger') + '">' + (data ? 'Active' : 'Inactive') + '</span>';
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
                        'targets': [0, 7]
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
