@extends('backend.layout.main')

 @section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Field Order')}} {{ $lims_field_order_data->reference_no }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('field-orders.index') }}">{{__('db.Field Orders')}}</a></li>
                    <li class="breadcrumb-item active">{{ $lims_field_order_data->reference_no }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="rounded-circle mb-3 d-inline-flex align-items-center justify-content-center bg-light" style="width:150px;height:150px;">
                        <i class="ti ti-package" style="font-size:60px;"></i>
                    </div>
                    <h4>{{ $lims_field_order_data->reference_no }}</h4>
                    <span class="badge badge-{{ $lims_field_order_data->status == 'delivered' || $lims_field_order_data->status == 'completed' ? 'success' : ($lims_field_order_data->status == 'cancelled' ? 'danger' : 'warning') }}">
                        {{ ucfirst($lims_field_order_data->status) }}
                    </span>
                    <div class="mt-3">
                        <a href="{{ route('field-orders.invoice', $lims_field_order_data->id) }}" class="btn btn-sm btn-outline-primary" target="_blank"><i class="ti ti-file-invoice"></i> {{__('db.Invoice')}}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Order Information')}}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tr><th style="width:35%">{{__('db.Reference No')}}</th><td>{{ $lims_field_order_data->reference_no }}</td></tr>
                        <tr><th>{{__('db.Order Type')}}</th><td>{{ ucfirst($lims_field_order_data->order_type) }}</td></tr>
                        <tr><th>{{__('db.Delivery Man')}}</th><td>{{ $lims_field_order_data->deliveryMan->name ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Customer')}}</th><td>{{ $lims_field_order_data->customer->name ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Warehouse')}}</th><td>{{ $lims_field_order_data->warehouse->name ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Status')}}</th><td>{{ ucfirst($lims_field_order_data->status) }}</td></tr>
                        <tr><th>{{__('db.Sub Total')}}</th><td>{{ number_format($lims_field_order_data->sub_total, 2) }}</td></tr>
                        <tr><th>{{__('db.Tax')}}</th><td>{{ number_format($lims_field_order_data->tax_amount, 2) }}</td></tr>
                        <tr><th>{{__('db.Shipping Cost')}}</th><td>{{ number_format($lims_field_order_data->shipping_cost, 2) }}</td></tr>
                        <tr><th>{{__('db.Discount')}}</th><td>{{ number_format($lims_field_order_data->discount_amount, 2) }}</td></tr>
                        <tr><th>{{__('db.Grand Total')}}</th><td>{{ number_format($lims_field_order_data->grand_total, 2) }}</td></tr>
                        <tr><th>{{__('db.Paid Amount')}}</th><td>{{ number_format($lims_field_order_data->paid_amount, 2) }}</td></tr>
                        <tr><th>{{__('db.Due Amount')}}</th><td>{{ number_format($lims_field_order_data->due_amount, 2) }}</td></tr>
                        <tr><th>{{__('db.Special Instructions')}}</th><td>{{ $lims_field_order_data->special_instructions ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Delivery Address')}}</th><td>{{ $lims_field_order_data->delivery_address ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.City')}}</th><td>{{ $lims_field_order_data->delivery_city ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Country')}}</th><td>{{ $lims_field_order_data->delivery_country ?? 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Products')}}</h5>
                </div>
                <div class="card-body">
                    @if($lims_field_order_data->products && $lims_field_order_data->products->isNotEmpty())
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{__('db.Product')}}</th>
                                    <th>{{__('db.Code')}}</th>
                                    <th>{{__('db.Qty')}}</th>
                                    <th>{{__('db.Unit Price')}}</th>
                                    <th>{{__('db.Sub Total')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lims_field_order_data->products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->code ?? 'N/A' }}</td>
                                        <td>{{ $product->qty }}</td>
                                        <td>{{ number_format($product->unit_price, 2) }}</td>
                                        <td>{{ number_format($product->sub_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">{{__('db.No products found')}}</p>
                    @endif
                </div>
            </div>

            @if($lims_field_order_data->payments && $lims_field_order_data->payments->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Payments')}}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{__('db.Reference No')}}</th>
                                <th>{{__('db.Method')}}</th>
                                <th>{{__('db.Amount')}}</th>
                                <th>{{__('db.date')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lims_field_order_data->payments as $payment)
                                <tr>
                                    <td>{{ $payment->reference_no }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
