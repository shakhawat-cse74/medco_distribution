@extends('backend.layout.main')
@push('css')
    @include('backend.layout.partials.datatable_css')
    <style>
    </style>
@endpush

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">{{ __('db.Delivery Return List') }}</h3>
                @can('delivery-sales-sale-return')
                    <a href="#" data-toggle="modal" data-target="#add-delivery-sale-return" class="btn btn-info"><i class="ti ti-plus"></i> {{ __('db.Add Return') }}</a>
                @endcan
            </div>
            <div class="card-body">
                <form action="{{ route('delivery-return.index') }}" method="get">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label>{{ __('db.Date') }}</label>
                            <div class="input-group">
                                <input type="text" class="daterangepicker-field form-control" value="{{ $starting_date }} To {{ $ending_date }}" required />
                                <input type="hidden" name="starting_date" value="{{ $starting_date }}" />
                                <input type="hidden" name="ending_date" value="{{ $ending_date }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
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
                        <div class="form-group mb-0">
                            <label>{{ __('db.Delivery Man') }}</label>
                            <select id="delivery_man_id" name="delivery_man_id" class="selectpicker form-control" data-live-search="true">
                                <option value="0">{{ __('db.All Delivery Man') }}</option>
                                @foreach ($lims_delivery_man_list as $dm)
                                    <option value="{{ $dm->id }}" {{ $dm->id == $delivery_man_id ? 'selected' : '' }}>{{ $dm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block"><i class="ti ti-filter"></i> {{ __('db.Filter') }}</button>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="return-table" class="table sale-list" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="not-exported"></th>
                                <th>{{ __('db.date') }}</th>
                                <th>{{ __('db.reference') }}</th>
                                <th>{{ __('db.Sale Reference') }}</th>
                                <th>{{ __('db.customer') }}</th>
                                <th>{{ __('db.Warehouse') }}</th>
                                <th>{{ __('db.Delivery Man') }}</th>
                                <th>{{ __('db.grand total') }}</th>
                                <th class="not-exported">{{ __('db.action') }}</th>
                            </tr>
                        </thead>
                        <tfoot class="tfoot active">
                            <tr>
                                <th></th>
                                <th>{{ __('db.Total') }}</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script type="text/javascript">
    $("ul#delivery").siblings('a').attr('aria-expanded', 'true');
    $("ul#delivery").addClass("show");
    $("ul#delivery #delivery-sale-return-menu").addClass("active");

    var all_permission = <?php echo json_encode($all_permission); ?>;
    var starting_date = $("input[name=starting_date]").val();
    var ending_date = $("input[name=ending_date]").val();
    var warehouse_id = $("#warehouse_id").val();
    var delivery_man_id = $("#delivery_man_id").val();

    var table = $('#return-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "{{ route('delivery-return.returnData') }}",
            data: function(d) {
                d.warehouse_id = $('#warehouse_id').val();
                d.delivery_man_id = $('#delivery_man_id').val();
                d.starting_date = $('input[name="starting_date"]').val();
                d.ending_date = $('input[name="ending_date"]').val();
            },
            dataType: "json",
            type: "post"
        },
        "createdRow": function(row, data, dataIndex) {
            $(row).addClass('return-link');
            $(row).attr('data-return', JSON.stringify(data['return']));
        },
        "columns": [
            { "data": "key" },
            { "data": "date" },
            { "data": "reference_no" },
            { "data": "sale_reference" },
            { "data": "customer" },
            { "data": "warehouse" },
            { "data": "delivery_man" },
            { "data": "grand_total" },
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
                'targets': [0, 7]
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
                text: '<i title="column visibility" class="ti ti-eye"></i>'
            }
        ],
        drawCallback: function() {
            var api = this.api();
            datatable_sum(api, false);
        }
    });

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows('.selected').any() && is_calling_first) {
            var rows = dt_selector.rows('.selected').indexes();
            $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, { page: 'current' }).data().sum().toFixed({{ config('decimal') }}));
        } else {
            $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, { page: 'current' }).data().sum().toFixed({{ config('decimal') }}));
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

    $(document).on("click", "tr.return-link td:not(:first-child, :last-child)", function() {
        var returns = JSON.parse($(this).parent().attr('data-return'));
        returnDetails(returns);
    });

    $(document).on("click", ".view", function() {
        var returns = JSON.parse($(this).closest('tr').attr('data-return'));
        returnDetails(returns);
    });

    $(document).on('click', '#print-btn', function() {
        var divContents = document.getElementById("return-details").innerHTML;
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<style>body{font-family: Arial, sans-serif; line-height: 1.5; padding: 20px;}');
        printWindow.document.write('table{width:100%; border-collapse: collapse; margin-top: 20px;} ');
        printWindow.document.write('th,td{border: 1px solid #ddd; padding: 8px; text-align: left;} ');
        printWindow.document.write('th{background-color: #f2f2f2;} .text-center{text-align:center;} ');
        printWindow.document.write('.modal-header{display:none;} @media print {.modal-dialog{display:none;}}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(divContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() { printWindow.print(); }, 250);
    });

    function returnDetails(returns) {
        $('input[name="return_id"]').val(returns[13]);
        var htmltext = '<strong>{{__("db.date")}}: </strong>'+returns[0]+'<br><strong>{{__("db.reference")}}: </strong>'+returns[1]+'<br><strong>{{__("db.Sale Reference")}}: </strong>'+returns[24]+'<br><strong>{{__("db.Warehouse")}}: </strong>'+returns[2]+'<br><strong>{{__("db.Currency")}}: </strong>'+returns[26];
        if(returns[27])
            htmltext += '<br><strong>{{__("db.Exchange Rate")}}: </strong>'+returns[27]+'<br>';
        else
            htmltext += '<br><strong>{{__("db.Exchange Rate")}}: </strong>N/A<br>';
        if(returns[25])
            htmltext += '<strong>{{__("db.Attach Document")}}: </strong><a href="documents/sale_return/'+returns[25]+'">Download</a><br>';
        htmltext += '<br><div class="row"><div class="col-md-6"><strong>{{__("db.Biller")}}:</strong><br>'+returns[3]+'<br>'+returns[4]+'<br>'+returns[5]+'<br>'+returns[6]+'<br>'+returns[7]+'<br>'+returns[8]+'</div><div class="col-md-6"><div class="float-right"><strong>{{__("db.Customer")}}:</strong><br>'+returns[9]+'<br>'+returns[10]+'<br>'+returns[11]+'<br>'+returns[12]+'</div></div></div>';
        $.get('/delivery-return/product_return/' + returns[13], function(data){
            $(".product-return-list tbody").remove();
            var name_code = data[0];
            var qty = data[1];
            var unit_code = data[2];
            var tax = data[3];
            var tax_rate = data[4];
            var discount = data[5];
            var subtotal = data[6];
            var batch_no = data[7];
            var newBody = $("<tbody>");
            $.each(name_code, function(index){
                var newRow = $("<tr>");
                var cols = '';
                cols += '<td><strong>' + (index+1) + '</strong></td>';
                cols += '<td>' + name_code[index] + '</td>';
                cols += '<td>' + (batch_no[index] || 'N/A') + '</td>';
                cols += '<td>' + qty[index] + ' ' + (unit_code[index] || '') + '</td>';
                cols += '<td>' + (subtotal[index] / qty[index]) + '</td>';
                cols += '<td>' + tax[index] + '(' + tax_rate[index] + '%)' + '</td>';
                cols += '<td>' + discount[index] + '</td>';
                cols += '<td>' + subtotal[index] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
            });

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=5><strong>{{__("db.Total")}}:</strong></td>';
            cols += '<td>' + returns[14] + '</td>';
            cols += '<td>' + returns[15] + '</td>';
            cols += '<td>' + returns[16] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=7><strong>{{__("db.Order Tax")}}:</strong></td>';
            cols += '<td>' + returns[17] + '(' + returns[18] + '%)' + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=7><strong>{{__("db.grand total")}}:</strong></td>';
            cols += '<td>' + returns[19] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            $("table.product-return-list").append(newBody);
        });
        var htmlfooter = '<p><strong>{{__("db.Return Note")}}:</strong> '+returns[20]+'</p><p><strong>{{__("db.Staff Note")}}:</strong> '+returns[21]+'</p><strong>{{__("db.Created By")}}:</strong><br>'+returns[22]+'<br>'+returns[23];
        $('#return-content').html(htmltext);
        $('#return-footer').html(htmlfooter);
        $('#return-details').modal('show');
    }
</script>

<div id="return-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row" style="width:100%">
                    <div class="col-md-6">
                        <form action="{{ route('delivery-return.sendmail') }}" method="post" class="sendmail-form">
                            @csrf
                            <input type="hidden" name="return_id">
                            <button class="btn btn-default btn-sm d-print-none" type="submit"><i class="ti ti-mail"></i> {{__('db.Email')}}</button>
                        </form>
                    </div>
                    <div class="col-md-6 d-print-none">
                        <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
                        <button type="button" id="print-btn" class="btn btn-default btn-sm"><i class="ti ti-printer"></i> {{__('db.Print')}}</button>
                    </div>
                    <div class="col-md-12">
                        <h3 id="exampleModalLabel" class="modal-title text-center container-fluid">{{gen_setting()->site_title}}</h3>
                    </div>
                    <div class="col-md-12 text-center">
                        <i style="font-size: 15px;">{{__('db.Return Details')}}</i>
                    </div>
                </div>
            </div>
            <div id="return-content" class="modal-body"></div>
            <br>
            <table class="table table-bordered product-return-list">
                <thead>
                    <th>#</th>
                    <th>{{__('db.product')}}</th>
                    <th>{{__('db.Batch No')}}</th>
                    <th>{{__('db.qty')}}</th>
                    <th>{{__('db.Unit Price')}}</th>
                    <th>{{__('db.Tax')}}</th>
                    <th>{{__('db.Discount')}}</th>
                    <th>{{__('db.Subtotal')}}</th>
                </thead>
                <tbody></tbody>
            </table>
            <div id="return-footer" class="modal-body"></div>
        </div>
    </div>
</div>

<div id="add-delivery-sale-return" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('delivery-return.create') }}" method="get">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{ __('db.Add Delivery Return') }}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ __('db.Sale Reference') }} *</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="DSR-XXXXX" required>
                </div>
                <small class="text-muted">{{ __('Enter the delivery sale reference number to create return') }}</small>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-secondary">{{ __('db.Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('db.Continue') }}</button>
            </div>
            </form>
        </div>
    </div>
</div>
@endpush
