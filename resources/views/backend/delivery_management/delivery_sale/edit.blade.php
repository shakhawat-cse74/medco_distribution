@extends('backend.layout.main') @section('content')
@push('css')
<style>
    @media print {
        .hidden-print {
            display: none !important;
        }
    }
    #product-results-container {
        display: none;
        background: #ffffff;
        position: absolute;
        max-height: 320px;
        overflow-y: auto;
        top: 100%;
        left: 0;
        width: 100%;
        z-index: 1000;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.12), 0 4px 6px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        margin-top: 4px;
    }
    body.dark-mode #product-results-container {
        background: #1e293b;
        border-color: rgba(255,255,255,0.1);
        box-shadow: 0 10px 25px rgba(0,0,0,0.4);
    }
    #product-results-container .product-img {
        padding: 9px 14px;
        color: #334155;
        font-size: 13.5px;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
    }
    body.dark-mode #product-results-container .product-img {
        color: #e2e8f0;
        border-bottom-color: rgba(255,255,255,0.05);
    }
    #product-results-container .product-img:last-child {
        border-bottom: none;
    }
    #product-results-container .product-img:hover {
        background-color: #7c5cc4;
        color: #ffffff;
    }

    .sale-section {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        padding: 20px 20px 10px 20px;
        margin-bottom: 20px;
        background: #fff;
    }
</style>
@endpush

<x-error-message key="not_permitted" />
<x-error-message key="error" />

<section id="pos-layout" class="forms hidden-print">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h4>{{__('db.Edit Delivery Sale')}} - {{ $lims_sale_data->reference_no }}</h4>
                <form action="{{ route('delivery-sale.update', $lims_sale_data->id) }}" method="post" enctype="multipart/form-data" class="payment-form">
                @csrf
                @method('PUT')
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.date')}}</label>
                                        <input type="text" name="created_at" class="form-control date" value="{{ date(config('date_format'), strtotime($lims_sale_data->created_at)) }}" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.customer')}} *</label>
                                        <div class="input-group pos">
                                            <select required name="customer_id" id="customer_id" class="selectpicker form-control" data-live-search="true" title="Select customer..." style="width: 100px">
                                            @foreach($lims_customer_list as $customer)
                                                <option value="{{$customer->id}}" @if($customer->id == $lims_sale_data->customer_id) selected @endif>{{$customer->name}} @if($customer->phone_number)({{$customer->phone_number}})@endif</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @if(isset(auth()->user()->warehouse_id))
                                <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{auth()->user()->warehouse_id}}" />
                                @else
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Warehouse')}} *</label>
                                        <select required name="warehouse_id" id="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select warehouse...">
                                            @foreach($lims_warehouse_list as $warehouse)
                                            <option value="{{$warehouse->id}}" @if($warehouse->id == $lims_sale_data->warehouse_id) selected @endif>{{$warehouse->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif
                                @if(isset(auth()->user()->biller_id))
                                <input type="hidden" name="biller_id" id="biller_id" value="{{auth()->user()->biller_id}}" />
                                @else
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Biller')}} *</label>
                                        <select required id="biller_id" name="biller_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select Biller...">
                                            @foreach($lims_biller_list as $biller)
                                            <option value="{{$biller->id}}" @if($biller->id == $lims_sale_data->biller_id) selected @endif>{{$biller->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{__('db.Currency')}} *</label>
                                        <select name="currency_id" id="currency" class="form-control selectpicker" data-toggle="tooltip" title="" data-original-title="Sale currency">
                                            @foreach($currency_list as $currency_data)
                                            <option value="{{$currency_data->id}}" data-rate="{{$currency_data->exchange_rate}}" @if($currency_data->id == $lims_sale_data->currency_id) selected @endif>{{$currency_data->code}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label>{{__('db.Exchange Rate')}} *</label>
                                    </div>
                                    <div class="form-group d-flex">
                                        <input class="form-control" type="text" id="exchange_rate" name="exchange_rate" value="{{$lims_sale_data->exchange_rate}}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Route')}} *</label>
                                        <select name="route_id" id="route_id" class="form-control selectpicker" data-live-search="true" title="Select Route...">
                                            <option value="">Select Route</option>
                                            @foreach($lims_route_list as $route)
                                            <option value="{{$route->id}}" @if($route->id == $lims_sale_data->route_id) selected @endif>{{$route->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Delivery Man')}} *</label>
                                        <select name="delivery_man_id" id="delivery_man_id" class="form-control selectpicker" data-live-search="true" title="Select Delivery Man...">
                                            <option value="">Select Delivery Man</option>
                                            @foreach($lims_delivery_man_list as $dm)
                                            <option value="{{$dm->id}}" @if($dm->id == $lims_sale_data->delivery_man_id) selected @endif data-routes="{{ implode(',', $dm->assigned_routes ?: []) }}">{{$dm->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label>{{__('db.Select Product')}}</label>
                                    <div class="search-box form-group mb-2" style="position:relative">
                                        <div class="input-group pos">
                                            <input style="border: 1px solid #7c5cc4;" type="text" name="product_code_name" id="product-search-input" placeholder="Scan/Search product by name/code/IMEI" class="form-control" autofocus />
                                            <button type="button" class="btn btn-primary" onclick="barcode()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upc" viewBox="0 0 16 16"><path d="M3 4.5a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0z"/></svg></button>
                                        </div>
                                        <div id="product-results-container" style="display:none;">
                                        </div>
                                        <div id="no-results-message" style="background-color: #f5f6f7;color: #666; margin-top: 5px;padding: 3px 5px; display: none;">No results found</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-5">
                                <div class="col-md-12">
                                    <h5>{{__('db.Order Table')}} *</h5>
                                    <div class="table-responsive mt-3">
                                        <table id="myTable" class="table table-hover order-list">
                                            <thead>
                                                <tr>
                                                    <th>{{__('db.product')}}</th>
                                                    <th>{{__('db.Quantity')}}</th>
                                                    <th>{{__('db.Net Unit Price')}}</th>
                                                    <th>{{__('db.Discount')}}</th>
                                                    <th>{{__('db.Tax')}}</th>
                                                    <th>{{__('db.Subtotal')}}</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-id">
                                                @foreach($lims_sale_data->productSales as $productSale)
                                                <tr>
                                                    <td>{{ $productSale->product->name ?? 'N/A' }} [{{ $productSale->product->code ?? 'N/A' }}]</td>
                                                    <td><input type="number" name="qty[]" class="form-control qty" value="{{ $productSale->qty }}" step="any"></td>
                                                    <td><input type="number" name="net_unit_price[]" class="form-control net_unit_price" value="{{ $productSale->net_unit_price }}" step="any"></td>
                                                    <td><input type="number" name="discount[]" class="form-control discount" value="{{ $productSale->discount }}" step="any"></td>
                                                    <td><input type="number" name="tax[]" class="form-control tax" value="{{ $productSale->tax_rate }}" step="any"></td>
                                                    <td class="subtotal">{{ $productSale->total }}</td>
                                                    <td><button type="button" class="btn btn-danger remove">×</button></td>
                                                    <input type="hidden" name="product_id[]" value="{{ $productSale->product_id }}">
                                                    <input type="hidden" name="variant_id[]" value="{{ $productSale->variant_id }}">
                                                    <input type="hidden" name="sale_unit_id[]" value="{{ $productSale->sale_unit_id }}">
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="tfoot active">
                                                <th>{{__('db.Total')}}</th>
                                                <th id="total-qty" class="text-center">{{ $lims_sale_data->total_qty }}</th>
                                                <th></th>
                                                <th id="total-discount">{{ number_format($lims_sale_data->total_discount, config('currency decimal', 2), '.', '') }}</th>
                                                <th id="total-tax">{{ number_format($lims_sale_data->total_tax, config('currency decimal', 2), '.', '') }}</th>
                                                <th id="total">{{ number_format($lims_sale_data->total_price, config('currency decimal', 2), '.', '') }}</th>
                                                <th></th>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="hidden" name="total_qty" id="total-qty-hidden" value="{{ $lims_sale_data->total_qty }}" />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="hidden" name="total_discount" id="total-discount-hidden" value="{{ $lims_sale_data->total_discount }}" />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="hidden" name="total_tax" id="total-tax-hidden" value="{{ $lims_sale_data->total_tax }}" />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="hidden" name="total_price" id="total-price-hidden" value="{{ $lims_sale_data->total_price }}" />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="hidden" name="item" id="item-hidden" value="{{ $lims_sale_data->item }}" />
                                        <input type="hidden" name="order_tax" id="order-tax-hidden" value="{{ $lims_sale_data->order_tax }}" />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="hidden" name="grand_total" id="grand-total-hidden" value="{{ $lims_sale_data->grand_total }}" />
                                        <input type="hidden" name="redeem_point" />
                                        <input type="hidden" name="coupon_active" value="0" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Order Tax')}}</label>
                                        <select class="form-control" name="order_tax_rate">
                                            <option value="0">No Tax</option>
                                            @foreach($lims_tax_list as $tax)
                                            <option value="{{$tax->rate}}" @if($tax->rate == $lims_sale_data->order_tax_rate) selected @endif>{{$tax->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Order Discount Type')}}</label>
                                        <select id="order-discount-type" name="order_discount_type" class="form-control">
                                            <option value="Flat" @if($lims_sale_data->order_discount_type == 'Flat') selected @endif>{{__('db.Flat')}}</option>
                                            <option value="Percentage" @if($lims_sale_data->order_discount_type == 'Percentage') selected @endif>{{__('db.Percentage')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('db.Order Discount Value') }}</label>
                                        <input type="number"
                                            name="order_discount_value"
                                            id="order-discount-val"
                                            class="form-control numkey"
                                            value="{{ $lims_sale_data->order_discount_value }}"
                                            step="0.01" />
                                        <input type="hidden" name="order_discount" id="order-discount" value="{{ $lims_sale_data->order_discount }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Shipping Cost')}}</label>
                                        <input type="number" name="shipping_cost" value="{{ $lims_sale_data->shipping_cost }}" class="form-control" step="any"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Attach Document')}}</label> <i class="ti ti-info-circle" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                        <input type="file" name="document" class="form-control" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Sale Status')}} *</label>
                                        <select name="sale_status" class="form-control">
                                            <option value="1" @if($lims_sale_data->sale_status == 1) selected @endif>{{__('db.Completed')}}</option>
                                            <option value="2" @if($lims_sale_data->sale_status == 2) selected @endif>{{__('db.Pending')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Payment Status')}} *</label>
                                        <select name="payment_status" id="payment_status" class="form-control">
                                            <option value="1" @if($lims_sale_data->payment_status == 1) selected @endif>{{__('db.Pending')}}</option>
                                            <option value="2" @if($lims_sale_data->payment_status == 2) selected @endif>{{__('db.Due')}}</option>
                                            <option value="3" @if($lims_sale_data->payment_status == 3) selected @endif>{{__('db.Partial')}}</option>
                                            <option value="4" @if($lims_sale_data->payment_status == 4) selected @endif>{{__('db.Paid')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Paid Amount')}}</label>
                                        <input type="number" name="paid_amount" class="form-control" value="{{ $lims_sale_data->paid_amount }}" step="any">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Sale Note')}}</label>
                                        <textarea rows="5" class="form-control" name="sale_note">{{ $lims_sale_data->sale_note }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Staff Note')}}</label>
                                        <textarea rows="5" class="form-control" name="staff_note">{{ $lims_sale_data->staff_note }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="hidden" name="draft" value="0" />
                                        <button id="submit-button" type="submit" class="btn btn-primary">{{__('db.update')}}</button>
                                        <a href="{{ route('delivery-sale.index') }}" class="btn btn-secondary">{{__('db.Cancel')}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-condensed totals mt-3">
                    <td><strong>{{__('db.Items')}}</strong>
                        <span class="pull-right" id="item">{{ $lims_sale_data->item }}</span>
                    </td>
                    <td><strong>{{__('db.Total')}}</strong>
                        <span class="pull-right" id="subtotal">{{ number_format($lims_sale_data->total_price, config('currency decimal', 2), '.', '') }}</span>
                    </td>
                    <td><strong>{{__('db.Order Tax')}}</strong>
                        <span class="pull-right" id="order_tax">{{ number_format($lims_sale_data->order_tax, config('currency decimal', 2), '.', '') }}</span>
                    </td>
                    <td><strong>{{__('db.Order Discount')}}</strong>
                        <span class="pull-right" id="order_discount">{{ number_format($lims_sale_data->order_discount, config('currency decimal', 2), '.', '') }}</span>
                    </td>
                    <td><strong>{{__('db.Shipping Cost')}}</strong>
                        <span class="pull-right" id="shipping_cost">{{ number_format($lims_sale_data->shipping_cost, config('currency decimal', 2), '.', '') }}</span>
                    </td>
                    <td><strong>{{__('db.grand total')}}</strong>
                        <span class="pull-right" id="grand_total">{{ number_format($lims_sale_data->grand_total, config('currency decimal', 2), '.', '') }}</span>
                    </td>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
