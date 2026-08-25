<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ url('logo', gen_setting()->site_logo) }}" />
    <title>Purchase_Record_{{ $lims_request_data->reference_no }}</title>
    <style type="text/css">
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 15px;
            background-color: #f4f6f9;
            color: #222;
            font-size: 13px;
        }

        .purchase-record-page {
            width: 210mm;
            min-height: 287mm;
            margin: 0 auto;
            background: #fff;
            padding: 20px 24px;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Top Header */
        .top-header {
            margin-bottom: 12px;
        }

        .header-logo-container {
            margin-bottom: 4px;
        }

        .header-logo-container img {
            max-height: 65px;
            max-width: 260px;
            object-fit: contain;
        }

        .company-title {
            font-size: 17px;
            font-weight: 800;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 4px 0 2px 0;
        }

        .company-meta {
            font-size: 12px;
            color: #444;
            line-height: 1.35;
        }

        /* Main Section Title */
        .record-title-divider {
            border-top: 1.5px solid #333;
            border-bottom: 1.5px solid #333;
            padding: 7px 0;
            text-align: center;
            margin: 12px 0 16px 0;
        }

        .record-title {
            font-size: 22px;
            font-weight: 900;
            color: #111;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0;
        }

        /* Meta Supplier & Ref Details */
        .meta-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .meta-container td {
            vertical-align: top;
        }

        .supplier-label {
            font-size: 12px;
            color: #444;
            margin-bottom: 2px;
        }

        .supplier-name {
            font-size: 14px;
            font-weight: bold;
            color: #111;
        }

        .supplier-address {
            font-size: 12px;
            color: #333;
            line-height: 1.35;
            margin-top: 2px;
        }

        .reference-box {
            text-align: right;
        }

        .reference-no {
            font-size: 22px;
            font-weight: 800;
            color: #2980b9;
            margin-bottom: 3px;
        }

        .reference-date {
            font-size: 13.5px;
            font-weight: bold;
            color: #2980b9;
        }

        /* Products Table */
        .record-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ccc;
            margin-top: 5px;
        }

        .record-table th {
            background-color: #2e86de;
            color: #ffffff;
            font-weight: bold;
            font-size: 12.5px;
            padding: 6px 8px;
            border: 1px solid #2e86de;
        }

        .record-table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            font-size: 12px;
            color: #222;
        }

        .record-table tr:nth-child(even) {
            background-color: #fafbfc;
        }

        /* Bottom Section: Notes & Totals */
        .bottom-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        .bottom-summary-table td {
            vertical-align: top;
        }

        .note-header {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #ccc;
            padding-bottom: 3px;
            width: 90%;
            margin-bottom: 5px;
        }

        .note-content {
            font-size: 11.5px;
            color: #555;
            line-height: 1.4;
            width: 90%;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 5px 8px;
            font-size: 13px;
        }

        .total-row {
            font-weight: bold;
            color: #111;
        }

        .grand-total-row {
            background-color: #2e86de;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 14px;
        }

        .grand-total-row td {
            color: #ffffff !important;
            padding: 6px 8px;
        }

        /* Signatures */
        .signature-container {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 30px 10px 10px 10px;
        }

        .sig-block {
            text-align: center;
            width: 220px;
        }

        .sig-line-bar {
            border-top: 1.5px dashed #444;
            margin-bottom: 6px;
            width: 100%;
        }

        .sig-label {
            font-size: 11.5px;
            color: #444;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
        }

        .hidden-print {
            text-align: center;
            margin-bottom: 15px;
        }

        .btn-action {
            display: inline-block;
            padding: 8px 18px;
            font-size: 13.5px;
            font-weight: bold;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            margin: 0 4px;
            border: none;
        }

        .btn-back {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-print {
            background-color: #2e86de;
            color: #fff;
        }

        @media print {
            html, body {
                background: none;
                padding: 0;
                margin: 0;
                height: 100%;
            }

            .hidden-print {
                display: none !important;
            }

            .purchase-record-page {
                width: 100% !important;
                height: 275mm !important;
                min-height: 275mm !important;
                max-height: 280mm !important;
                padding: 10mm 10mm 10mm 10mm !important;
                margin: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                box-sizing: border-box !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }

            .content-body {
                flex: 1 1 auto !important;
            }

            .signature-container {
                margin-top: auto !important;
                padding-bottom: 5mm !important;
                page-break-inside: avoid !important;
            }

            @page {
                size: A4 portrait;
                margin: 5mm 6mm;
            }
        }
    </style>
</head>
<body>

    <div class="hidden-print">
        <a href="{{ route('purchase_requests.index') }}" class="btn-action btn-back"><i class="ti ti-arrow-left"></i> Back to Requests</a>
        <button onclick="window.print();" class="btn-action btn-print"><i class="ti ti-printer"></i> Print Purchase Record</button>
    </div>

    <div class="purchase-record-page">
        <div class="content-body">
            <!-- Top Header with Logo and Company info -->
            <div class="top-header">
                <div class="header-logo-container">
                    @if (gen_setting()->site_logo || ($invoice_settings && $invoice_settings->company_logo))
                        <img src="{{ ($invoice_settings && $invoice_settings->company_logo) ? url('invoices', $invoice_settings->company_logo) : url('logo', gen_setting()->site_logo) }}" alt="Logo">
                    @endif
                </div>
                <div class="company-title">
                    {{ gen_setting()->company_name ?? 'MEDCO DISTRIBUTIONS WNY INC' }}
                </div>
                <div class="company-meta">
                    {{ $lims_request_data->warehouse->address ?? (gen_setting()->address ?? '1285 William Street buffalo NY') }}
                </div>
                <div class="company-meta">
                    {{ $lims_request_data->warehouse->phone ?? (gen_setting()->phone ?? '9292809807') }}
                </div>
                <div class="company-meta">
                    {{ $lims_request_data->warehouse->email ?? (gen_setting()->email ?? 'medcoworld@gmail.com') }}
                </div>
            </div>

            <!-- PURCHASE RECORD Title with Divider Lines -->
            <div class="record-title-divider">
                <h1 class="record-title">PURCHASE RECORD</h1>
            </div>

            <!-- Supplier & Reference Meta -->
            <table class="meta-container">
                <tr>
                    <td style="width: 60%;">
                        <div class="supplier-label">Purchase From</div>
                        <div class="supplier-name">
                            {{ $lims_request_data->supplier ? ($lims_request_data->supplier->company_name ?: $lims_request_data->supplier->name) : 'Individual Supplier' }}
                        </div>
                        <div class="supplier-address">
                            @if($lims_request_data->supplier)
                                @php
                                    $suppAddress = array_filter([
                                        $lims_request_data->supplier->address,
                                        $lims_request_data->supplier->city,
                                        $lims_request_data->supplier->state,
                                        $lims_request_data->supplier->postal_code
                                    ]);
                                @endphp
                                {{ implode(', ', $suppAddress) ?: 'Address Not Provided' }}
                            @endif
                        </div>
                    </td>
                    <td style="width: 40%;" class="reference-box">
                        <div class="reference-no">{{ $lims_request_data->reference_no }}</div>
                        <div class="reference-date">{{ \Carbon\Carbon::parse($lims_request_data->created_at)->format('m-d-Y') }}</div>
                    </td>
                </tr>
            </table>

            <!-- Products Table with Signature Blue Header -->
            <table class="record-table">
                <thead>
                    <tr>
                        <th style="width: 6%; text-align: center;">Sr no.</th>
                        <th style="width: 52%; text-align: left;">Product</th>
                        <th style="width: 14%; text-align: right;">Qty</th>
                        <th style="width: 14%; text-align: right;">Rate</th>
                        <th style="width: 14%; text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lims_product_request_data as $key => $item)
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $key + 1 }}</td>
                            <td style="text-align: left; font-weight: bold;">
                                {{ $item->product ? $item->product->name : 'Product' }}
                            </td>
                            <td style="text-align: right;">{{ number_format($item->qty, 2) }}</td>
                            <td style="text-align: right;">{{ number_format($item->net_unit_cost, 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Bottom Section: Notes on Left & Totals on Right -->
            <table class="bottom-summary-table">
                <tr>
                    <td style="width: 60%;">
                        <div class="note-header">Please Note</div>
                        <div class="note-content">
                            {{ $lims_request_data->note ?: 'No special notes.' }}
                        </div>
                    </td>
                    <td style="width: 40%;">
                        <table class="totals-table">
                            <tr class="total-row">
                                <td style="text-align: left; width: 45%;">Total</td>
                                <td style="text-align: right; width: 55%;">{{ config('currency') ?? '$' }} {{ number_format($lims_request_data->total_cost, 2) }}</td>
                            </tr>
                            <tr class="grand-total-row">
                                <td style="text-align: left;">Grand Total</td>
                                <td style="text-align: right;">{{ config('currency') ?? '$' }} {{ number_format($lims_request_data->grand_total, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Bottom Signatures -->
        <div class="signature-container">
            <div class="sig-block sig-left">
                <div class="sig-line-bar"></div>
                <div class="sig-label">Supplier Signature</div>
            </div>
            <div class="sig-block sig-right">
                <div class="sig-line-bar"></div>
                <div class="sig-label">Authorise Signature</div>
            </div>
        </div>
    </div>

    @if(request()->get('is_print') || request()->query('is_print'))
    <script type="text/javascript">
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
    @endif

</body>
</html>
