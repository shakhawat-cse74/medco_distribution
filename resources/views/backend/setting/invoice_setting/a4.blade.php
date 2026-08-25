<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ url('logo', gen_setting()->site_logo) }}" />
    <title>{{ ($lims_customer_data->name ?? 'Customer') . '_Invoice_' . $lims_sale_data->reference_no }}</title>
    <style type="text/css">
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 10px;
            background-color: #f4f6f9;
            color: #000;
            font-size: 13px;
        }

        .invoice-page {
            width: 210mm;
            min-height: 287mm;
            margin: 0 auto;
            background: #fff;
            padding: 12px;
            box-sizing: border-box;
        }

        .invoice-box {
            border: 1.5px solid #222;
            padding: 14px 16px 20px 16px;
            min-height: 278mm;
            position: relative;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Top Header */
        .header-section {
            text-align: center;
            margin-bottom: 8px;
        }

        .header-logo {
            text-align: center;
            margin-bottom: 3px;
        }

        .header-logo img {
            max-height: 75px;
            max-width: 220px;
            object-fit: contain;
        }

        .invoice-title {
            color: #d84315;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: 1px;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .company-address {
            font-size: 12px;
            color: #222;
            margin-top: 3px;
        }

        .company-contact {
            font-size: 11.5px;
            color: #222;
            margin-top: 2px;
        }

        /* Metadata table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .meta-table td {
            vertical-align: top;
            font-size: 12.5px;
            line-height: 1.4;
        }

        /* Products Table */
        .main-invoice-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #777;
            margin-top: 4px;
        }

        .main-invoice-table th {
            border: 1px solid #777;
            padding: 4px 5px;
            color: #b33923;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            background-color: #fff;
        }

        .main-invoice-table td {
            border: 1px solid #777;
            padding: 4px 5px;
            font-size: 12px;
        }

        /* Totals section below table (borderless) */
        .totals-container-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 6px;
        }

        .totals-container-table td {
            border: none;
            padding: 0;
        }

        .totals-inner-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .totals-inner-table td {
            border: none !important;
            padding: 2.5px 4px !important;
            font-size: 13px;
        }

        .signature-section {
            width: 100%;
            margin-top: 30px;
        }

        .sig-line {
            border-top: 1.5px solid #000;
            width: 200px;
            margin: 0 auto;
            padding-top: 4px;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
        }

        .page-num {
            text-align: right;
            font-size: 11px;
            color: #333;
            margin-top: 12px;
        }

        .hidden-print {
            margin-bottom: 12px;
            text-align: center;
        }

        .btn-action {
            display: inline-block;
            padding: 7px 18px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            margin: 0 5px;
            border: none;
        }

        .btn-back {
            background-color: #17a2b8;
            color: #fff;
        }

        .btn-print {
            background-color: #7c5cc4;
            color: #fff;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }

            .hidden-print {
                display: none !important;
            }

            .invoice-page {
                width: 100% !important;
                min-height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .invoice-box {
                border: 1.5px solid #222 !important;
                padding: 14px 16px 15px 16px !important;
                min-height: 275mm !important;
            }

            @page {
                size: A4 portrait;
                margin: 5mm 7mm;
            }
        }
    </style>
</head>
<body>

    <div class="hidden-print">
        <a href="{{ route('sales.index') }}" class="btn-action btn-back"><i class="ti ti-arrow-left"></i> {{ __('db.Back') }}</a>
        <button onclick="window.print();" class="btn-action btn-print"><i class="ti ti-printer"></i> {{ __('db.Print') }}</button>
    </div>

    <div class="invoice-page">
        <div class="invoice-box">
            <div class="top-content">
                <!-- Header: Logo, Title, Company Name, Address, Contact -->
                <div class="header-section">
                    <div class="header-logo">
                        @if (gen_setting()->site_logo || $invoice_settings->company_logo)
                            <img src="{{ $invoice_settings->company_logo ? url('invoices', $invoice_settings->company_logo) : url('logo', gen_setting()->site_logo) }}" alt="Logo">
                        @endif
                    </div>
                    <div class="invoice-title">INVOICE</div>
                    <div class="company-name">
                        {{ gen_setting()->company_name ?? ($lims_biller_data->company_name ?? 'MEDCO DISTRIBUTIONS WNY INC') }}
                    </div>
                    <div class="company-address">
                        {{ $lims_warehouse_data->address ?? (gen_setting()->address ?? '1285 William Street,Buffalo,NY-14206') }}
                    </div>
                    <div class="company-contact">
                        @php
                            $mobile = $lims_warehouse_data->phone ?? '9292809807';
                            $phone = $lims_biller_data->phone_number ?? '7167872330';
                            $email = $lims_warehouse_data->email ?? ($lims_biller_data->email ?? (gen_setting()->email ?? 'medcodistributionwny@gmail.com'));
                        @endphp
                        Mobile : {{ $mobile }}@if($phone && $phone != $mobile), Phone : {{ $phone }}@endif , E-mail : {{ $email }}
                    </div>
                </div>

                <!-- Meta Details: Bill To & Invoice Info -->
                <table class="meta-table">
                    <tr>
                        <!-- Bill To -->
                        <td style="width: 58%;">
                            <div>
                                <strong>Bill To:</strong> <strong style="text-transform: uppercase;">{{ $lims_customer_data->company_name ? $lims_customer_data->company_name : $lims_customer_data->name }}</strong>
                            </div>
                            @if($lims_customer_data->company_name && $lims_customer_data->name && $lims_customer_data->company_name != $lims_customer_data->name)
                                <div>{{ $lims_customer_data->name }}</div>
                            @endif
                            @php
                                $custAddr = $lims_customer_data->address;
                                $cityStateZip = array_filter([$lims_customer_data->city, $lims_customer_data->state, $lims_customer_data->postal_code]);
                                $cityStateZipStr = implode(', ', $cityStateZip);
                            @endphp
                            @if($custAddr || $cityStateZipStr)
                                <div>
                                    {{ $custAddr }}@if($custAddr && $cityStateZipStr), @endif{{ $cityStateZipStr }}
                                </div>
                            @endif
                            @if($lims_customer_data->phone_number)
                                <div>{{ $lims_customer_data->phone_number }}</div>
                            @endif
                        </td>

                        <!-- Invoice Info -->
                        <td style="width: 42%;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="font-weight: bold; width: 95px; padding: 1px 0;">Invoice No.</td>
                                    <td style="font-weight: bold; padding: 1px 0;">{{ $lims_sale_data->reference_no }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; padding: 1px 0;">Date</td>
                                    <td style="font-weight: bold; padding: 1px 0;">{{ \Carbon\Carbon::parse($lims_sale_data->created_at)->format('m/d/Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; padding: 1px 0;">Prepared By</td>
                                    <td style="padding: 1px 0;">{{ $lims_bill_by['name'] ?? ($lims_biller_data->name ?? 'Admin') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Main Products Table (Only items, no lines/dividers below) -->
                <table class="main-invoice-table">
                    <thead>
                        <tr>
                            <th style="width: 4%; text-align: center;">SL</th>
                            <th style="width: 44%; text-align: left;">DESCRIPTION</th>
                            <th style="width: 11%; text-align: right;">UNIT PRICE</th>
                            <th style="width: 9%; text-align: center;">Unit Type</th>
                            <th style="width: 7%; text-align: right;">QTY</th>
                            <th style="width: 12%; text-align: right;">DISCOUNT</th>
                            <th style="width: 13%; text-align: right;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($line_items as $key => $item)
                            @php
                                $itemAmount = ($item->net_unit_price * $item->qty) - ($item->discount * $item->qty);
                                $unitCodeDisplay = strtoupper($item->unit_code ?: 'PC');
                            @endphp
                            <tr>
                                <td style="text-align: center; font-weight: bold; vertical-align: middle;">
                                    {{ $key + 1 }}
                                </td>
                                <td style="text-align: left; font-weight: bold; vertical-align: middle; line-height: 1.3;">
                                    {!! $item->product_name !!}
                                    @if ($item->imei_number && !str_contains($item->imei_number, 'null'))
                                        <div style="font-size: 11px; font-weight: normal; color: #555;">IMEI: {{ $item->imei_number }}</div>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: bold; vertical-align: middle;">
                                    {{ number_format($item->net_unit_price, 2) }}
                                </td>
                                <td style="text-align: center; font-weight: bold; vertical-align: middle;">
                                    {{ $unitCodeDisplay }}
                                </td>
                                <td style="text-align: right; font-weight: bold; vertical-align: middle;">
                                    {{ number_format($item->qty, 2) }}
                                </td>
                                <td style="text-align: right; vertical-align: middle;">
                                    {{ number_format($item->discount * $item->qty, 2) }}
                                </td>
                                <td style="text-align: right; font-weight: bold; vertical-align: middle;">
                                    {{ number_format($itemAmount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Clean Borderless Totals Section Below Items Table -->
                <table class="totals-container-table">
                    <tr>
                        <!-- Left space for notes if any -->
                        <td style="width: 58%; vertical-align: top; padding-top: 6px;">
                            @if ($lims_sale_data->sale_note)
                                <div style="font-size: 12px; color: #444;"><strong>Note:</strong> {{ $lims_sale_data->sale_note }}</div>
                            @endif
                        </td>

                        <!-- Right Totals List (clean, no extra boxes/borders) -->
                        <td style="width: 42%; vertical-align: top; padding-top: 6px;">
                            <table class="totals-inner-table">
                                <tr>
                                    <td style="font-weight: bold; text-align: left; width: 55%; color: #000;">Sub Total</td>
                                    <td style="font-weight: bold; text-align: right; width: 45%; color: #000;">{{ number_format($lims_sale_data->total_price - $lims_sale_data->total_discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; text-align: left; color: #000;">TAX</td>
                                    <td style="text-align: right; color: #000;">{{ number_format($lims_sale_data->total_tax + $lims_sale_data->order_tax, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; text-align: left; color: #000;">Total</td>
                                    <td style="font-weight: bold; text-align: right; color: #000;">{{ number_format($lims_sale_data->grand_total, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; text-align: left; color: #000; border-bottom: 1px solid #777 !important; padding-bottom: 3px !important;">Paid</td>
                                    <td style="text-align: right; color: #000; border-bottom: 1px solid #777 !important; padding-bottom: 3px !important;">{{ number_format($lims_sale_data->paid_amount, 2) }}</td>
                                </tr>
                                @php
                                    $previousDue = $prevDue > 0 ? $prevDue : 0;
                                    $balanceDue = ($lims_sale_data->grand_total - $lims_sale_data->paid_amount) + $previousDue;
                                @endphp
                                <tr>
                                    <td style="font-weight: bold; text-align: left; color: #d84315; padding-top: 4px !important;">Previous Due</td>
                                    <td style="font-weight: bold; text-align: right; color: #d84315; padding-top: 4px !important;">{{ number_format($previousDue, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; text-align: left; color: #d84315; font-size: 13.5px;">Balance Due</td>
                                    <td style="font-weight: bold; text-align: right; color: #d84315; font-size: 13.5px;">{{ number_format($balanceDue, 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Footer Signatures & Page Number -->
            <div class="bottom-content">
                <div class="signature-section">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 45%; text-align: center; vertical-align: bottom;">
                                <div class="sig-line">Customer Signature</div>
                            </td>
                            <td style="width: 10%;"></td>
                            <td style="width: 45%; text-align: center; vertical-align: bottom;">
                                <div class="sig-line">Authorised Signature</div>
                            </td>
                        </tr>
                    </table>
                    <div class="page-num">Page 1 of 1</div>
                </div>
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
