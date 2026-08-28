@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Customer Ledger')}} - {{ $customer->name }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('delivery-men.index') }}">{{__('db.delivery_men_list')}}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('delivery-men.assignedCustomers', $lims_delivery_man_data->id) }}">{{__('db.Assigned Customers')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.Customer Ledger')}}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>{{__('db.Total Orders')}}</h6>
                    <h3>{{ $totalOrders }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>{{__('db.Total Order Amount')}}</h6>
                    <h3>{{ number_format($totalOrderAmount, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>{{__('db.Total Paid')}}</h6>
                    <h3>{{ number_format($totalPaid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>{{__('db.Total Due')}}</h6>
                    <h3>{{ number_format($totalDue, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Transactions')}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{__('db.Date')}}</th>
                                    <th>{{__('db.Reference No')}}</th>
                                    <th>{{__('db.Type')}}</th>
                                    <th>{{__('db.Amount')}}</th>
                                    <th>{{__('db.Payment Method')}}</th>
                                    <th>{{__('db.Status')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ date('d M, Y', strtotime($order->created_at)) }}</td>
                                        <td>{{ $order->reference_no }}</td>
                                        <td>{{__('db.Order')}}</td>
                                        <td>{{ number_format($order->grand_total, 2) }}</td>
                                        <td>-</td>
                                        <td>
                                            <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @foreach($order->payments as $payment)
                                        <tr>
                                            <td>{{ date('d M, Y', strtotime($payment->created_at)) }}</td>
                                            <td>{{ $payment->reference_no }}</td>
                                            <td>{{__('db.Payment')}}</td>
                                            <td>{{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ ucfirst($payment->payment_method) }}</td>
                                            <td>
                                                <span class="badge badge-success">{{__('db.Paid')}}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
