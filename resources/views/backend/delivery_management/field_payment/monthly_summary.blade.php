@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.monthly_collection_summary')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('field-payments.index') }}">{{__('db.field_payments')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.monthly_summary')}}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.monthly_collection_summary')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="m-b-0">{{__('db.month')}}</h6>
                                    <h2 class="m-t-10">{{ $data['month'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="m-b-0">{{__('db.total_payments')}}</h6>
                                    <h2 class="m-t-10">{{ $data['total_payments'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="m-b-0">{{__('db.total_amount')}}</h6>
                                    <h2 class="m-t-10">{{ number_format($data['total_amount'], 2) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="m-b-0">{{__('db.average_amount')}}</h6>
                                    <h2 class="m-t-10">{{ $data['total_payments'] > 0 ? number_format($data['total_amount'] / $data['total_payments'], 2) : '0.00' }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>{{__('db.payment_details')}}</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{__('db.sl')}}</th>
                                            <th>{{__('db.order_reference')}}</th>
                                            <th>{{__('db.delivery_man')}}</th>
                                            <th>{{__('db.customer')}}</th>
                                            <th>{{__('db.payment_method')}}</th>
                                            <th>{{__('db.amount')}}</th>
                                            <th>{{__('db.created_at')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['payments'] as $index => $payment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>#{{ $payment->fieldOrder->order_number ?? 'N/A' }}</td>
                                            <td>{{ $payment->fieldOrder->deliveryMan->name ?? 'N/A' }}</td>
                                            <td>{{ $payment->fieldOrder->customer->name ?? 'N/A' }}</td>
                                            <td>{{ $payment->payment_method }}</td>
                                            <td>{{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ $payment->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <button onclick="window.print()" class="btn btn-primary">{{__('db.print_report')}}</button>
                            <a href="{{ route('field-payments.index') }}" class="btn btn-secondary">{{__('db.back_to_list')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection