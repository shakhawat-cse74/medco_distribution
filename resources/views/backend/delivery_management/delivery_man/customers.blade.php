@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Assigned Customers')}} - {{ $lims_delivery_man_data->name }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('delivery-men.index') }}">{{__('db.delivery_men_list')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.Assigned Customers')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Assigned Customers')}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="customer-table">
                            <thead>
                                <tr>
                                    <th>{{__('db.Name')}}</th>
                                    <th>{{__('db.Phone')}}</th>
                                    <th>{{__('db.Total Orders')}}</th>
                                    <th>{{__('db.Total Due')}}</th>
                                    <th>{{__('db.Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                    <tr>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->phone_number }}</td>
                                        <td>{{ $customer->fieldOrders->count() }}</td>
                                        <td>{{ number_format($customer->fieldOrders->sum('due_amount'), 2) }}</td>
                                        <td>
                                            <a href="{{ route('delivery-men.customerOrderHistory', [$lims_delivery_man_data->id, $customer->id]) }}" class="btn btn-sm btn-info">
                                                <i class="ti ti-list"></i> {{__('db.Orders')}}
                                            </a>
                                            <a href="{{ route('delivery-men.customerLedger', [$lims_delivery_man_data->id, $customer->id]) }}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-book"></i> {{__('db.Ledger')}}
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
