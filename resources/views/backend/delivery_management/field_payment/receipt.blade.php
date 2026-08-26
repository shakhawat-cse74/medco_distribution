@extends('backend.layout.main')

@section('content')
<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 200px; }
        .receipt-box { border: 2px solid #333; padding: 20px; margin: 20px 0; }
        .receipt-header { text-align: center; margin-bottom: 20px; }
        .receipt-title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .receipt-info { margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .info-label { font-weight: bold; }
        .info-value { }
        .payment-details { margin-top: 30px; }
        .payment-table { width: 100%; border-collapse: collapse; }
        .payment-table th, .payment-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .payment-table th { background-color: #f2f2f2; }
        .total-section { margin-top: 20px; text-align: right; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .grand-total { font-size: 18px; font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; }
        @media print {
            body { margin: 0; }
            .receipt-box { border: none; }
            .no-print { display: none; }
        }
        .no-print { margin: 20px 0; }
    </style>
</head>
<body>
    <div class="receipt-box">
        <div class="receipt-header">
            <div class="receipt-title">{{ gen_setting()->site_title ?? 'BanglaSoft' }}</div>
            <div>{{__('db.payment_receipt')}}</div>
        </div>
        
        <div class="receipt-info">
            <div class="info-row">
                <div class="info-label">{{__('db.payment_reference')}}:</div>
                <div class="info-value">{{ $lims_payment_data->reference_no ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{__('db.date_time')}}:</div>
                <div class="info-value">{{ $lims_payment_data->created_at->format('Y-m-d H:i:s') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{__('db.paid_by')}}:</div>
                <div class="info-value">{{ $lims_payment_data->createdBy->name ?? 'N/A' }}</div>
            </div>
        </div>
        
        <div class="payment-details">
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>{{__('db.description')}}</th>
                        <th>{{__('db.amount')}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Order #{{ $lims_payment_data->fieldOrder->order_number ?? 'N/A' }}</td>
                        <td>{{ number_format($lims_payment_data->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>{{__('db.payment_method')}}: {{ $lims_payment_data->payment_method }}</td>
                        <td>{{ number_format($lims_payment_data->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="total-section">
            <div class="total-row">
                <span>{{__('db.total_amount')}}:</span>
                <span class="grand-total">{{ number_format($lims_payment_data->amount, 2) }}</span>
            </div>
        </div>
        
        <div class="footer">
            <p>{{__('db.thank_you_for_your_business')}}</p>
            <p>{{ gen_setting()->site_title ?? 'BanglaSoft' }} | {{ env('VERSION') }}</p>
        </div>
    </div>
    
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">{{__('db.print_receipt')}}</button>
        <a href="{{ route('field-payments.index') }}" class="btn btn-secondary">{{__('db.back_to_list')}}</a>
    </div>
</body>
</html>
@endsection