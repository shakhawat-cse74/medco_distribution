@extends('backend.layout.main')
@push('css')
    @include('backend.layout.partials.datatable_css')
@endpush

@section('content')
<style>
    .btn-icon i{margin-right:5px}
    .top-fields{margin-top:10px;position: relative;}
    .top-fields label {font-size:11px;font-weight:600;margin-left:10px;padding:0 3px;position:absolute;top:-8px;z-index:9;background:#fff}
    .top-fields input, .top-fields select{font-size:13px;height:45px}
</style>

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        @can('delivery-sales-sale-exchange')
            <a href="{{ route('delivery-exchange.create') }}" class="btn btn-info add-sale-btn btn-icon"><i class="ti ti-plus"></i> {{ __('db.Add Exchange') }}</a>
        @endcan

        <button type="button" class="btn btn-warning btn-icon" id="toggle-filter">
            <i class="ti ti-filter"></i> {{ __('db.Filter Exchange') }}
        </button>

        <div class="card mt-3 mb-2">
            <div class="card-body" id="filter-card" style="display: none;">
                <form action="{{ route('delivery-exchange.index') }}" method="get">
                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="form-group top-fields">
                            <label>{{ __('db.date') }}</label>
                            <input type="text" class="daterangepicker-field form-control" value="{{ $starting_date }} To {{ $ending_date }}" required />
                            <input type="hidden" name="starting_date" value="{{ $starting_date }}" />
                            <input type="hidden" name="ending_date" value="{{ $ending_date }}" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group top-fields">
                            <label>{{ __('db.Warehouse') }}</label>
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true">
                                <option value="0">{{ __('db.All Warehouse') }}</option>
                                @foreach ($lims_warehouse_list as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $warehouse->id == $warehouse_id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group top-fields">
                            <label>{{ __('db.Delivery Man') }}</label>
                            <select id="delivery_man_id" name="delivery_man_id" class="selectpicker form-control" data-live-search="true">
                                <option value="0">{{ __('db.All Delivery Man') }}</option>
                                @foreach ($lims_delivery_man_list as $dm)
                                    <option value="{{ $dm->id }}" {{ $dm->id == $delivery_man_id ? 'selected' : '' }}>{{ $dm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="exchange-table" class="table sale-list" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{ __('db.date') }}</th>
                    <th>{{ __('db.reference') }}</th>
                    <th>{{ __('db.Sale Reference') }}</th>
                    <th>{{ __('db.customer') }}</th>
                    <th>{{ __('db.Warehouse') }}</th>
                    <th>{{ __('db.Delivery Man') }}</th>
                    <th>{{ __('db.Type') }}</th>
                    <th>{{ __('db.Amount') }}</th>
                    <th class="not-exported">{{ __('db.action') }}</th>
                </tr>
            </thead>
            <tfoot class="tfoot active">
                <th></th>
                <th>{{ __('db.Total') }}</th>
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
    $("ul#delivery").siblings('a').attr('aria-expanded', 'true');
    $("ul#delivery").addClass("show");
    $("ul#delivery #delivery-sale-exchange-menu").addClass("active");

    $('#toggle-filter').on('click', function() {
        $('#filter-card').slideToggle('slow');
    });

    var all_permission = <?php echo json_encode($all_permission); ?>;
    var starting_date = $("input[name=starting_date]").val();
    var ending_date = $("input[name=ending_date]").val();
    var warehouse_id = $("#warehouse_id").val();
    var delivery_man_id = $("#delivery_man_id").val();

    var table = $('#exchange-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "{{ route('delivery-exchange.exchangeData') }}",
            data: function(d) {
                d.warehouse_id = $('#warehouse_id').val();
                d.delivery_man_id = $('#delivery_man_id').val();
                d.starting_date = $('input[name="starting_date"]').val();
                d.ending_date = $('input[name="ending_date"]').val();
            },
            dataType: "json",
            type: "post"
        },
        "columns": [
            { "data": "key" },
            { "data": "date" },
            { "data": "reference_no" },
            { "data": "sale_reference" },
            { "data": "customer" },
            { "data": "warehouse" },
            { "data": "delivery_man" },
            { "data": "payment_type" },
            { "data": "amount" },
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
                'targets': [0, 9]
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
            { extend: 'pdf', text: '<i class="ti ti-file-type-pdf"></i>' },
            { extend: 'excel', text: '<i class="ti ti-file-type-xls"></i>' },
            { extend: 'csv', text: '<i class="ti ti-file-type-csv"></i>' },
            { extend: 'print', text: '<i class="ti ti-printer"></i>' },
            { extend: 'colvis', text: '<i class="ti ti-eye"></i>' }
        ],
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    });

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows('.selected').any() && is_calling_first) {
            var rows = dt_selector.rows('.selected').indexes();
            $(dt_selector.column(8).footer()).html(dt_selector.cells(rows, 8, { page: 'current' }).data().sum().toFixed({{ config('decimal') }}));
        } else {
            $(dt_selector.column(8).footer()).html(dt_selector.cells(rows, 8, { page: 'current' }).data().sum().toFixed({{ config('decimal') }}));
        }
    }

    $('#warehouse_id, #delivery_man_id').on('change', function() {
        table.ajax.reload();
    });

    $('.daterangepicker-field').on('apply.daterangepicker', function(ev, picker) {
        $('input[name="starting_date"]').val(picker.startDate.format('YYYY-MM-DD'));
        $('input[name="ending_date"]').val(picker.endDate.format('YYYY-MM-DD'));
        table.ajax.reload();
    });
</script>
@endpush
