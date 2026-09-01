@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Order History')}} - {{ $customer->name }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('delivery-men.index') }}">{{__('db.delivery_men_list')}}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('delivery-men.assignedCustomers', $lims_delivery_man_data->id) }}">{{__('db.Assigned Customers')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.Order History')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Order History')}} - {{ $customer->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{__('db.Reference No')}}</th>
                                    <th>{{__('db.Date')}}</th>
                                    <th>{{__('db.Status')}}</th>
                                    <th>{{__('db.Grand Total')}}</th>
                                    <th>{{__('db.Paid')}}</th>
                                    <th>{{__('db.Due')}}</th>
                                    <th>{{__('db.Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ $order->reference_no }}</td>
                                        <td>{{ date('d M, Y', strtotime($order->created_at)) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($order->grand_total, 2) }}</td>
                                        <td>{{ number_format($order->paid_amount, 2) }}</td>
                                        <td>{{ number_format($order->due_amount, 2) }}</td>
                                        <td>
                                            <a href="{{ route('field-orders.show', $order->id) }}" class="btn btn-sm btn-info" target="_blank">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
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
