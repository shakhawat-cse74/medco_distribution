@extends('backend.layout.main')

@section('content')
<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">{{ __('db.Invoice') }} - {{ $lims_sale_data->reference_no }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>{{ __('db.Customer Information') }}</h5>
                        <p><strong>{{ __('db.Customer') }}:</strong> {{ $lims_sale_data->customer->name ?? 'N/A' }}</p>
                        <p><strong>{{ __('db.Phone') }}:</strong> {{ $lims_sale_data->customer->phone_number ?? 'N/A' }}</p>
                        <p><strong>{{ __('db.Address') }}:</strong> {{ $lims_sale_data->customer->address ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <h5>{{ __('db.Sale Information') }}</h5>
                        <p><strong>{{ __('db.Reference') }}:</strong> {{ $lims_sale_data->reference_no }}</p>
                        <p><strong>{{ __('db.Date') }}:</strong> {{ date(config('date_format'), strtotime($lims_sale_data->created_at)) }}</p>
                        <p><strong>{{ __('db.Warehouse') }}:</strong> {{ $lims_sale_data->warehouse->name ?? 'N/A' }}</p>
                        <p><strong>{{ __('db.Delivery Man') }}:</strong> {{ $lims_sale_data->deliveryMan->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <hr>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>{{ __('db.Product') }}</th>
                            <th>{{ __('db.Quantity') }}</th>
                            <th>{{ __('db.Unit Price') }}</th>
                            <th>{{ __('db.Tax') }}</th>
                            <th>{{ __('db.Discount') }}</th>
                            <th>{{ __('db.Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lims_product_sale_data as $product)
                        <tr>
                            <td>{{ $product->product->name ?? 'N/A' }}</td>
                            <td>{{ $product->qty }}</td>
                            <td>{{ number_format($product->net_unit_price, 2) }}</td>
                            <td>{{ number_format($product->tax, 2) }}</td>
                            <td>{{ number_format($product->discount, 2) }}</td>
                            <td>{{ number_format($product->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="row mt-4">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th>{{ __('db.Sub Total') }}</th>
                                <td>{{ number_format($lims_sale_data->total_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Order Tax') }}</th>
                                <td>{{ number_format($lims_sale_data->order_tax, 2) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Discount') }}</th>
                                <td>{{ number_format($lims_sale_data->order_discount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Shipping Cost') }}</th>
                                <td>{{ number_format($lims_sale_data->shipping_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Grand Total') }}</th>
                                <td><strong>{{ number_format($lims_sale_data->grand_total, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
