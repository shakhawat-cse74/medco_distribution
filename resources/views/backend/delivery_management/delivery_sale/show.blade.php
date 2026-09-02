@extends('backend.layout.main')

@section('content')
<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">{{ __('db.Delivery Sale Details') }} - {{ $lims_sale_data->reference_no }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>{{ __('db.Customer Information') }}</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>{{ __('db.Customer') }}</th>
                                <td>{{ $lims_sale_data->customer->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Phone') }}</th>
                                <td>{{ $lims_sale_data->customer->phone_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Address') }}</th>
                                <td>{{ $lims_sale_data->customer->address ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>{{ __('db.Sale Information') }}</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>{{ __('db.Reference') }}</th>
                                <td>{{ $lims_sale_data->reference_no }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Warehouse') }}</th>
                                <td>{{ $lims_sale_data->warehouse->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Delivery Man') }}</th>
                                <td>{{ $lims_sale_data->deliveryMan->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Status') }}</th>
                                <td>
                                    @if($lims_sale_data->sale_status == 1)
                                        <span class="badge badge-success">{{ __('db.Completed') }}</span>
                                    @elseif($lims_sale_data->sale_status == 2)
                                        <span class="badge badge-danger">{{ __('db.Pending') }}</span>
                                    @elseif($lims_sale_data->sale_status == 3)
                                        <span class="badge badge-warning">{{ __('db.Draft') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>{{ __('db.Products') }}</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('db.Product') }}</th>
                                    <th>{{ __('db.Quantity') }}</th>
                                    <th>{{ __('db.Unit Price') }}</th>
                                    <th>{{ __('db.Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lims_sale_data->productSales as $product)
                                <tr>
                                    <td>{{ $product->product->name ?? 'N/A' }}</td>
                                    <td>{{ $product->qty }}</td>
                                    <td>{{ number_format($product->net_unit_price, 2) }}</td>
                                    <td>{{ number_format($product->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 offset-md-6">
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
                            <tr>
                                <th>{{ __('db.Paid') }}</th>
                                <td>{{ number_format($lims_sale_data->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('db.Due') }}</th>
                                <td>{{ number_format($lims_sale_data->grand_total - $lims_sale_data->paid_amount, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <a href="{{ url('sales/gen_invoice', $lims_sale_data->id) }}" class="btn btn-primary">
                        <i class="ti ti-printer"></i> {{ __('db.Generate Invoice') }}
                    </a>
                    <a href="{{ route('delivery-sale.index') }}" class="btn btn-secondary">
                        {{ __('db.Back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
