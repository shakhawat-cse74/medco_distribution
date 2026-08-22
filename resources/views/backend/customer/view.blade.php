@extends('backend.layout.main')

@push('css')
    @include('backend.layout.partials.datatable_css')
<style>
    .dataTables_wrapper.container-fluid {
        padding: 0;
    }
    .flex-fill {
        background: #FFF;
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <x-success-message key="message" />
    <x-error-message key="not_permitted" />

    <button id="full-page-print-btn" type="button" class="btn btn-default btn-sm">
        <i class="ti ti-printer"></i> {{__('db.Print')}}
    </button>
    <!-- Customer Header -->
    <div class="card mt-5" id="customer-info">
        <div class="card-header d-flex justify-content-between align-items-start">

            <!-- Left: name, phone, email -->
            <div>
                <span><h4 class="mb-1">{{ $lims_customer_data->name ?? '-' }}</h4>Customer</span>
                <p class="mb-0"><strong>Email:</strong> {{ $lims_customer_data->email ?? '-' }}</p>
                <p class="mb-0"><strong>Phone:</strong> {{ $lims_customer_data->phone_number ?? '-' }}</p>
            </div>

            <!-- Right: address -->
            <div class="text-end">
                <p class="mb-0">
                    <strong>Address:</strong> {{ $lims_customer_data->address ?? '-' }}<br>
                    <strong>City:</strong> {{ $lims_customer_data->city ?? '-' }}<br>
                    <strong>Country:</strong> {{ $lims_customer_data->country ?? '-' }}
                </p>
            </div>

        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mt-4" id="lpp-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="#ledger-latest" role="tab" data-toggle="tab">
                Ledger
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#sales-latest" role="tab" data-toggle="tab">
                {{ __('db.Sale') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#sales-payment-latest" role="tab" data-toggle="tab">
                {{ __('db.Sale Payment') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#installments-latest" role="tab" data-toggle="tab">
                {{ __('db.Instalment') }}
            </a>
        </li>
    </ul>

    <div id="ledger-summery"
     class="d-flex mt-3 text-center d-none"
     style="gap: 0.75rem;">

        <div class="flex-fill p-4">
            <strong>Opening Balance</strong><br>
            <h5>{{ number_format($opening_balance, 2) }}</h5>
        </div>

        <div class="flex-fill p-4">
            <strong>Total Sales</strong><br>
            <h5>{{ number_format($total_sales, 2) }}</h5>
        </div>

        <div class="flex-fill p-4">
            <strong>Total Paid</strong><br>
            <h5>{{ number_format($total_paid, 2) }}</h5>
        </div>

        <div class="flex-fill p-4">
            <strong>Total Returns</strong><br>
            <h5>{{ number_format($total_returns, 2) }}</h5>
        </div>

        <div class="flex-fill p-4">
            <strong>Balance Due</strong><br>
            <h5 class="text-danger">{{ number_format($balance_due, 2) }}</h5>
        </div>

    </div>


    <div class="tab-content mb-5">

        <!-- LEDGER TAB -->
        <div role="tabpanel" class="tab-pane fade show active" id="ledger-latest">
            {{-- <div class="col-md-3">
                <div class="form-group top-fields">
                    <label>{{__('db.date')}}</label>
                    <input type="text" class="daterangepicker-field form-control" value="{{$starting_date}} To {{$ending_date}}" required />
                    <input type="hidden" name="starting_date" value="{{$starting_date}}" />
                    <input type="hidden" name="ending_date" value="{{$ending_date}}" />
                </div>
            </div> --}}
            <div class="table-responsive">
                <table id="recent-ledger" class="table w-100">
                    <thead>
                    <tr>
                        <th>{{ __('db.date') }}</th>
                        <th>Type</th>
                        <th>{{ __('db.reference') }}</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- SALES TAB -->
        <div role="tabpanel" class="tab-pane fade" id="sales-latest">
            <div class="table-responsive">
                <table id="recent-sales" class="table w-100">
                    <thead>
                        <tr>
                            <th>{{ __('db.date') }}</th>
                            <th>{{ __('db.reference') }}</th>
                            <th>{{ __('db.Warehouse') }}</th>
                            <th>{{ __('db.Sale Status') }}</th>
                            <th>{{ __('db.Payment Status') }}</th>
                            <th>{{ __('db.grand total') }}</th>
                            <th>{{ __('db.Paid Amount') }}</th>
                            <th>{{ __('db.Due') }}</th>
                            <th class="not-exported">{{ __('db.action') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- SALES PAYMENT TAB -->
        <div role="tabpanel" class="tab-pane fade" id="sales-payment-latest">
            <div class="table-responsive">
                <table id="recent-sales-payment" class="table w-100">
                    <thead>
                        <tr>
                            <th>{{ __('db.date') }}</th>
                            <th>{{ __('db.reference') }}</th>
                            <th>{{ __('db.Amount') }}</th>
                            <th>{{ __('db.Payment Method') }}</th>
                            <th>{{ __('db.payment_at') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- INSTALMENT TAB -->
        <div role="tabpanel" class="tab-pane fade" id="installments-latest">
            <div class="table-responsive">
                <table id="recent-installments" class="table w-100">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Sale Reference</th>
                            <th>Purchase Reference</th>
                            <th>Instalment No</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- SALE DETAILS MODAL -->
    @include('backend.sale.sale_details_modal')

</div>
@endsection


@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script>
$(function () {

    // LEDGER TABLE
    $('#recent-ledger').DataTable({
        ajax: "{{ route('customers.ledger', $lims_customer_data->id) }}",
        columns: [
            { data: 'date' },
            { data: 'type' },
            { data: 'reference' },
            { data: 'debit' },
            { data: 'credit' },
            { data: 'balance' }
        ],
        order: [[0, 'desc']],
        responsive: true,
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: "pdfHtml5",
                text: '<i class="ti ti-file-type-pdf"></i>',
                className: 'btn btn-sm btn-danger me-1',
                title: 'Customer Ledger',
                footer: true,
                exportOptions: {
                    columns: ':visible:not(.not-exported)'
                },
                customize: function (doc) {
                    doc.defaultStyle.fontSize = 9;
                    doc.styles.tableHeader.fontSize = 10;
                    doc.content.splice(0, 1); // remove auto title if needed
                }
            },
            {
                extend: "csvHtml5",
                text: '<i title="export to csv" class="ti ti-file-type-csv"></i>',
                className: 'btn btn-sm btn-primary',
                footer: true,
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                }
            },
        ]
    });

    // SALES TABLE
    $('#recent-sales').DataTable({
        ajax: "{{ route('sales.customer', $lims_customer_data->id) }}",
        columns: [
            { data: 'date' },
            { data: 'reference' },
            { data: 'warehouse' },
            { data: 'sale_status' },
            { data: 'payment_status' },
            { data: 'grand_total' },
            { data: 'paid_amount' },
            { data: 'payment_due' },
            {
                data: 'id',
                render: function(data){
                    return `
                        <a href="javascript:void(0)" class="btn btn-sm btn-info view-sale" data-id="${data}">
                            <i class="ti ti-eye"></i>
                        </a>
                        <a href="/sales/${data}/edit" class="btn btn-sm btn-warning">
                            <i class="ti ti-edit"></i>
                        </a>
                    `;
                },
                orderable: false,
                searchable: false
            }
        ],
        order: [[0, 'desc']],
        responsive: true,
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: "pdfHtml5",
                text: '<i class="ti ti-file-type-pdf"></i>',
                className: 'btn btn-sm btn-danger me-1',
                title: 'Customer Sales',
                footer: true,
                exportOptions: {
                    columns: ':visible:not(.not-exported)'
                },
                customize: function (doc) {
                    doc.defaultStyle.fontSize = 9;
                    doc.styles.tableHeader.fontSize = 10;
                    doc.content.splice(0, 1); // remove auto title if needed
                }
            },
            {
                extend: "csvHtml5",
                text: '<i title="export to csv" class="ti ti-file-type-csv"></i>',
                className: 'btn btn-sm btn-primary',
                footer: true,
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                }
            },
        ]
    });

    // SALES PAYMENT TABLE
    $('#recent-sales-payment').DataTable({
        ajax: "{{ route('customers.payments', $lims_customer_data->id) }}",
        columns: [
            { data: 'created_at' },
            { data: 'payment_reference' },
            { data: 'amount' },
            { data: 'paying_method' },
            { data: 'payment_at' },
        ],
        order: [[0, 'desc']],
        responsive: true,
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: "pdfHtml5",
                text: '<i class="ti ti-file-type-pdf"></i>',
                className: 'btn btn-sm btn-danger me-1',
                title: 'Customer Sales',
                footer: true,
                exportOptions: {
                    columns: ':visible:not(.not-exported)'
                },
                customize: function (doc) {
                    doc.defaultStyle.fontSize = 9;
                    doc.styles.tableHeader.fontSize = 10;
                    doc.content.splice(0, 1); // remove auto title if needed
                }
            },
            {
                extend: "csvHtml5",
                text: '<i title="export to csv" class="ti ti-file-type-csv"></i>',
                className: 'btn btn-sm btn-primary',
                footer: true,
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                }
            },
        ]
    });

    // INSTALMENTS TABLE
    $('#recent-installments').DataTable({
        ajax: "{{ route('customers.installments', $lims_customer_data->id) }}",
        columns: [
            { data: 'date' },
            { data: 'sale_reference' },
            { data: 'purchase_reference' },
            { data: 'installment_no' },
            { data: 'amount' },
            { data: 'status' },
            { data: 'payment_date' }
        ],
        order: [[0, 'desc']],
        responsive: true,
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: "pdfHtml5",
                text: '<i class="ti ti-file-type-pdf"></i>',
                className: 'btn btn-sm btn-danger me-1',
                title: 'Customer Instalments',
                footer: true,
                exportOptions: {
                    columns: ':visible:not(.not-exported)'
                }
            },
            {
                extend: "csvHtml5",
                text: '<i class="ti ti-file-type-csv"></i>',
                className: 'btn btn-sm btn-primary',
                footer: true,
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                }
            }
        ]
    });

    // VIEW SALE MODAL
    $(document).on("click", ".view-sale", function () {
        let table = $('#recent-sales').DataTable();
        let sale = table.row($(this).parents('tr')).data();
        saleDetails(sale);
    });

    $('#ledger-summery').removeClass('d-none'); // default open

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        let target = $(e.target).attr("href");
        if (target === '#ledger-latest') {
            $('#ledger-summery').removeClass('d-none');
        } else {
            $('#ledger-summery').addClass('d-none');
        }
    });

    $("#full-page-print-btn").on("click", function () {
        var customerInfo = $("#customer-info").prop("outerHTML");

        // Clone tabs and highlight only the active one
        var tabsNav = $("#lpp-tabs").clone();
        tabsNav.find("li").removeClass("active"); // remove all active
        var activeIndex = $(".tab-pane.active").index();
        tabsNav.find("li").eq(activeIndex).addClass("active");
        tabsNav = tabsNav.prop("outerHTML");

        // Ledger summary
        var ledgerSummary = $("#ledger-summery").prop("outerHTML");

        // Active tab content
        var activeTab = $(".tab-pane.active").prop("outerHTML");

        var printWindow = window.open('', '', 'height=900,width=1400');

        printWindow.document.write('<html><head><title>Customer Ledger</title>');

        printWindow.document.write(`
            <style>
                @page { size: landscape; }
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }

                /* NAV TABS HORIZONTAL */
                .nav-tabs {
                    display: flex;
                    border-bottom: 2px solid #000;
                    margin-bottom: 15px;
                    padding-left: 0;
                    list-style: none;
                }
                .nav-tabs li { margin-right: 8px; }
                .nav-tabs li a {
                    text-decoration: none;
                    padding: 6px 14px;
                    border: 1px solid #000;
                    display: inline-block;
                    color: #000;
                    background: #f2f2f2;
                }
                .nav-tabs li.active a {
                    background: #719cdd;
                    color: #fff;
                    font-weight: bold;
                    border-color: #000;
                }

                /* Ledger summary horizontal */
                #ledger-summery {
                    display: flex !important;
                    flex-wrap: wrap;
                    gap: 10px;
                    margin-top: 15px;
                }
                #ledger-summery > div {
                    flex: 1;
                    padding: 10px;
                    border: 1px solid #000;
                    border-radius: 4px;
                    background: #f8f8f8;
                    text-align: center;
                }

                /* Hide buttons & datatable controls */
                .btn,
                .dataTables_length,
                .dataTables_filter,
                .dataTables_info,
                .dataTables_paginate,
                .dt-buttons {
                    display: none !important;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                }
                table, th, td { border: 1px solid #000; }
                th, td { padding: 6px; text-align: left; }
                .text-end { text-align: right; }
                .text-center { text-align: center; }

                /* Hide action column */
                table th:last-child,
                table td:last-child {
                    display: none !important;
                }
            </style>
        `);

        printWindow.document.write('</head><body>');
        printWindow.document.write(customerInfo);
        printWindow.document.write(tabsNav);
        printWindow.document.write(ledgerSummary);
        printWindow.document.write(activeTab);
        printWindow.document.write('</body></html>');

        printWindow.document.close();

        setTimeout(function () {
            printWindow.print();
            printWindow.close();
        }, 700);
    });

});

@include('backend.sale.sale_details_function');

</script>
@endpush
