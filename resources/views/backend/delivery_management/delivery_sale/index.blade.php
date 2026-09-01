@extends('backend.layout.main')
@push('css')
    @include('backend.layout.partials.datatable_css')
@endpush

@section('content')
<style>
    .btn-icon i{margin-right:5px}
    .top-fields{margin-top:10px;position: relative;}
    .top-fields label {font-size:11px;font-weight:600;margin-left:10px;padding:0 3px;position:absolute;top:-8px;z-index:9;}
    .top-fields input, .top-fields select{font-size:13px;height:45px}
</style>

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        @can('delivery-sales-add')
            <a href="{{route('delivery-sale.create')}}" class="btn btn-info add-sale-btn btn-icon"><i class="ti ti-plus"></i> {{__('db.Add Sale')}}</a>
        @endcan
        <a href="{{route('delivery-sale.pos')}}" class="btn btn-primary add-sale-btn btn-icon"><i class="ti ti-shopping-cart"></i> {{__('db.POS')}}</a>
        <a href="{{route('delivery-sale.giftCardList')}}" class="btn btn-secondary add-sale-btn btn-icon"><i class="ti ti-gift"></i> {{__('db.Gift Card List')}}</a>
        <a href="{{route('delivery-sale.challanList')}}" class="btn btn-warning add-sale-btn btn-icon"><i class="ti ti-file"></i> {{__('db.Challan List')}}</a>
        <a href="{{route('delivery-sale.saleReturn')}}" class="btn btn-info add-sale-btn btn-icon"><i class="ti ti-arrow-back"></i> {{__('db.Sale Return')}}</a>
        <a href="{{route('delivery-sale.installmentList')}}" class="btn btn-dark add-sale-btn btn-icon"><i class="ti ti-calendar"></i> {{__('db.Installment List')}}</a>

        <button type="button" class="btn btn-warning btn-icon" id="toggle-filter">
            <i class="ti ti-filter"></i> {{ __('db.Filter Sales') }}
        </button>

        <div class="card mt-3 mb-2">
            <div class="card-body" id="filter-card" style="display: none;">
                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="form-group top-fields">
                            <label>{{__('db.date')}}</label>
                            <input type="text" class="daterangepicker-field form-control" value="{{$starting_date}} To {{$ending_date}}" required />
                            <input type="hidden" name="starting_date" value="{{$starting_date}}" />
                            <input type="hidden" name="ending_date" value="{{$ending_date}}" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group top-fields">
                            <label>{{__('db.Warehouse')}}</label>
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true">
                                <option value="0">{{__('db.All Warehouse')}}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                    <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group top-fields">
                            <label>{{__('db.Delivery Man')}}</label>
                            <select id="delivery_man_id" name="delivery_man_id" class="selectpicker form-control" data-live-search="true">
                                <option value="0">{{__('db.All Delivery Man')}}</option>
                                @foreach($lims_delivery_man_list as $dm)
                                    <option value="{{$dm->id}}">{{$dm->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group top-fields">
                            <label>{{__('db.Route')}}</label>
                            <select id="route_id" name="route_id" class="selectpicker form-control" data-live-search="true">
                                <option value="0">{{__('db.All Routes')}}</option>
                                @foreach($lims_route_list as $route)
                                    <option value="{{$route->id}}">{{$route->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group top-fields">
                            <label>{{__('db.Sale Status')}}</label>
                            <select id="sale-status" class="form-control" name="sale_status">
                                <option value="0">{{__('db.All')}}</option>
                                <option value="1">{{__('db.Completed')}}</option>
                                <option value="2">{{__('db.Pending')}}</option>
                                <option value="3">{{__('db.Draft')}}</option>
                                <option value="4">{{__('db.Returned')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group top-fields">
                            <label>{{__('db.Payment Status')}}</label>
                            <select id="payment-status" class="form-control" name="payment_status">
                                <option value="0">{{__('db.All')}}</option>
                                <option value="1">{{__('db.Pending')}}</option>
                                <option value="2">{{__('db.Due')}}</option>
                                <option value="3">{{__('db.Partial')}}</option>
                                <option value="4">{{__('db.Paid')}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="sale-table" class="table sale-list" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{__('db.date')}}</th>
                    <th>{{__('db.reference')}}</th>
                    <th>{{__('db.customer')}}</th>
                    <th>{{__('db.Warehouse')}}</th>
                    <th>{{__('db.Delivery Man')}}</th>
                    <th>{{__('db.Sale Status')}}</th>
                    <th>{{__('db.Payment Status')}}</th>
                    <th>{{__('db.grand total')}} ({{ config('currency') }})</th>
                    <th>{{__('db.Paid')}} ({{ config('currency') }})</th>
                    <th>{{__('db.Due')}} ({{ config('currency') }})</th>
                    <th class="not-exported">{{__('db.action')}}</th>
                </tr>
            </thead>
            <tfoot class="tfoot active">
                <th></th>
                <th>{{__('db.Total')}}</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tfoot>
        </table>
    </div>
</section>
@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script type="text/javascript">
    $("ul#delivery").siblings('a').attr('aria-expanded','true');
    $("ul#delivery").addClass("show");
    $("ul#delivery #delivery-sale-menu").addClass("active");

    $('#toggle-filter').on('click', function() {
        $('#filter-card').slideToggle('slow');
    });

    var table = $('#sale-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "{{ route('delivery-sale.saleData') }}",
            data: function(d) {
                d.warehouse_id = $('#warehouse_id').val();
                d.delivery_man_id = $('#delivery_man_id').val();
                d.route_id = $('#route_id').val();
                d.sale_status = $('#sale-status').val();
                d.payment_status = $('#payment-status').val();
                d.starting_date = $('input[name="starting_date"]').val();
                d.ending_date = $('input[name="ending_date"]').val();
            },
            dataType: "json",
            type: "GET"
        },
        "columns": [
            { "data": "key" },
            { "data": "date" },
            { "data": "reference_no" },
            { "data": "customer" },
            { "data": "warehouse" },
            { "data": "delivery_man" },
            { "data": "sale_status" },
            { "data": "payment_status" },
            { "data": "grand_total" },
            { "data": "paid_amount" },
            { "data": "due" },
            { "data": "options" }
        ],
        'language': {
            'lengthMenu': '_MENU_ {{ __("db.records per page") }}',
            "info": '<small>{{ __("db.Showing") }} _START_ - _END_ (_TOTAL_)</small>',
            "search": '{{ __("db.Search") }}',
            'paginate': {
                'previous': '<i class="ti ti-chevron-left"></i>',
                'next': '<i class="ti ti-chevron-right"></i>'
            }
        },
        order: [[1, 'desc']],
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 11]
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
        'select': { style: 'multi', selector: 'td:first-child' },
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            { extend: 'pdf', text: '<i title="export to pdf" class="ti ti-file-type-pdf"></i>' },
            { extend: 'excel', text: '<i title="export to excel" class="ti ti-file-type-xls"></i>' },
            { extend: 'csv', text: '<i title="export to csv" class="ti ti-file-type-csv"></i>' },
            { extend: 'print', text: '<i title="print" class="ti ti-printer"></i>' },
            { extend: 'colvis', text: '<i title="column visibility" class="ti ti-eye"></i>' }
        ]
    });

    $('#warehouse_id, #delivery_man_id, #route_id, #sale-status, #payment-status').on('change', function() {
        table.ajax.reload();
    });

    $('.daterangepicker-field').on('apply.daterangepicker', function(ev, picker) {
        $('input[name="starting_date"]').val(picker.startDate.format('YYYY-MM-DD'));
        $('input[name="ending_date"]').val(picker.endDate.format('YYYY-MM-DD'));
        table.ajax.reload();
    });
</script>
@endpush
