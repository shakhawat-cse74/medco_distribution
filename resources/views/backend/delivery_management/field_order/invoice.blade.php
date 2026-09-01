<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="{{ url('logo', gen_setting()->site_logo) }}" />
    <title>Field Order Invoice - {{ $lims_field_order_data->reference_no }}</title>
    <style type="text/css">
        span, td { font-size: 13px; line-height: 1.4; }
        @media print {
            .hidden-print { display: none !important; }
        }
        table, tr, td { font-family: sans-serif; border-collapse: collapse; }
    </style>
</head>

<body>
    <div class="hidden-print">
        <table><tr>
            <td><a href="{{ url()->previous() }}" class="btn btn-info"><i class="ti ti-arrow-left"></i> {{ __('db.Back') }}</a></td>
            <td><button onclick="window.print();" class="btn btn-primary"><i class="ti ti-printer"></i> {{ __('db.Print') }}</button></td>
        </tr></table>
        <br>
    </div>

    <table style="width: 100%;border-collapse: collapse;">
        <tr>
            <td colspan="2" style="padding:9px 0;width:40%">
                <h1 style="margin:0">{{ gen_setting()->company_name ?? 'BanglaSoft' }}</h1>
                <div><span>{{ __('db.Address') }}:</span>&nbsp;&nbsp;<span>{{ $lims_field_order_data->delivery_address ?? 'N/A' }}</span></div>
                <div><span>{{ __('db.Phone') }}:</span>&nbsp;&nbsp;<span>{{ $lims_field_order_data->deliveryMan->phone_number ?? 'N/A' }}</span></div>
                @if(gen_setting()->vat_registration_number)
                    <div><span>{{ __('db.VAT Number') }}:</span>&nbsp;&nbsp;<span>{{ gen_setting()->vat_registration_number }}</span></div>
                @endif
            </td>
            <td style="width:30%; text-align: middle; vertical-align: top;">
                @if(gen_setting()->site_logo)
                    <img src="{{ url('logo', gen_setting()->site_logo) }}" height="60" width="120" style="margin:5px 0;">
                @endif
            </td>
            <td style="padding:5px -19px;width:30%;text-align:right;">
                <div style="display: flex;justify-content: space-between;border-bottom:1px solid #aaa">
                    <span>{{ __('db.Reference No') }}:</span> <span>{{ $lims_field_order_data->reference_no }}</span>
                </div>
                <div style="display: flex;justify-content: space-between;border-bottom:1px solid #aaa">
                    <span>{{ __('db.date') }}:</span> <span>{{ $lims_field_order_data->created_at->format('Y-m-d') }}</span>
                </div>
                <div style="display: flex;justify-content: space-between;border-bottom:1px solid #aaa">
                    <span>{{ __('db.Status') }}:</span> <span>{{ ucfirst($lims_field_order_data->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <br>

    <table style="width: 100%;border-collapse: collapse;border: 1px solid #aaa;">
        <tr class="table-header" style="background-color: #014b94; color: white;">
            <th style="border: 1px solid #aaa; padding: 8px;">{{ __('db.Delivery Man') }}</th>
            <th style="border: 1px solid #aaa; padding: 8px;">{{ __('db.Customer') }}</th>
            <th style="border: 1px solid #aaa; padding: 8px;">{{ __('db.Warehouse') }}</th>
            <th style="border: 1px solid #aaa; padding: 8px;">{{ __('db.Order Type') }}</th>
        </tr>
        <tr>
            <td style="border: 1px solid #aaa; padding: 8px;">{{ $lims_field_order_data->deliveryMan->name ?? 'N/A' }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;">{{ $lims_field_order_data->customer->name ?? 'N/A' }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;">{{ $lims_field_order_data->warehouse->name ?? 'N/A' }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;">{{ ucfirst($lims_field_order_data->order_type) }}</td>
        </tr>
    </table>

    <br>

    <table style="width: 100%;border-collapse: collapse;border: 1px solid #aaa;">
        <tr class="table-header" style="background-color: #014b94; color: white;">
            <th style="border: 1px solid #aaa; padding: 8px;">{{ __('db.Product') }}</th>
            <th style="border: 1px solid #aaa; padding: 8px;">{{ __('db.Code') }}</th>
            <th style="border: 1px solid #aaa; padding: 8px;">{{ __('db.Qty') }}</th>
            <th style="border: 1px solid #aaa; padding: 8px;">{{ __('db.Unit Price') }}</th>
            <th style="border: 1px solid #aaa; padding: 8px;">{{ __('db.Sub Total') }}</th>
        </tr>
        @foreach($lims_field_order_data->products as $product)
        <tr>
            <td style="border: 1px solid #aaa; padding: 8px;">{{ $product->name }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;">{{ $product->code ?? 'N/A' }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;">{{ $product->qty }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;">{{ number_format($product->unit_price, 2) }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;">{{ number_format($product->sub_total, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <br>

    <table style="width: 50%;border-collapse: collapse;border: 1px solid #aaa;margin-left: auto;">
        <tr>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;">{{ __('db.Sub Total') }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;">{{ number_format($lims_field_order_data->sub_total, 2) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;">{{ __('db.Tax') }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;">{{ number_format($lims_field_order_data->tax_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;">{{ __('db.Shipping Cost') }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;">{{ number_format($lims_field_order_data->shipping_cost, 2) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;">{{ __('db.Discount') }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;">{{ number_format($lims_field_order_data->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;font-weight: bold;">{{ __('db.Grand Total') }}</td>
            <td style="border: 1px solid #aaa; padding: 8px;text-align: right;font-weight: bold;">{{ number_format($lims_field_order_data->grand_total, 2) }}</td>
        </tr>
    </table>

    <br><br>

    <table style="width: 100%;border-collapse: collapse;">
        <tr>
            <td style="text-align: left;width: 50%;">
                <strong>{{ __('db.Special Instructions') }}:</strong><br>
                {{ $lims_field_order_data->special_instructions ?? 'N/A' }}
            </td>
        </tr>
    </table>

    <br><br>
    <div style="text-align: center;">
        <p>Thank you for your business!</p>
    </div>

</body>
</html>
