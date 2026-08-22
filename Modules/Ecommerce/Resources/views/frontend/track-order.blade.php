@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description') @endsection

@push('css')
<style>
    .mute {opacity: 0.5}
    .order-details span {color: #212222; font-weight: 500;}
    .table-content table td {font-size: 14px;}
    .product-img {max-width: 50px; margin-right:15px}
</style>
@endpush

@section('content')
<!--Breadcrumb Area start-->
<div class="breadcrumb-section">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="page-title">{{__('db.Track Order')}}</h1>
            </div>
        </div>
    </div>
</div>
<!--Breadcrumb Area ends-->

<section class="user-login-section mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-3">

                @if(!empty($sale))
                <div class="order-details card mb-5">
                    <div class="card-header">
                        <h3 class="text-center mb-0">Order ID- {{ $sale->reference_no }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <span class="mute">{{__('db.Time')}} : </span>
                                    <span>{{ date('d-m-Y h:i:s', strtotime($sale->created_at)) }}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="mute">{{__('db.Order Status')}} : </span>
                                    @if($sale->sale_status == 1)
                                    <span>{{__('db.Complete')}}</span>
                                    @elseif($sale->sale_status == 2)
                                    <span>{{__('db.Pending')}}</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <span class="mute">{{__('db.Payment Method')}} : </span>
                                    <span>{{ $sale->payment_mode }}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="mute">{{__('db.Payment Status')}} : </span>
                                    @if($sale->payment_status == 1)
                                    <span>{{__('db.Pending')}}</span>
                                    @elseif($sale->payment_status == 4)
                                    <span>{{__('db.Paid')}}</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    @if($delivery != 0)
                                    <span class="mute">{{__('db.Shipping Status')}} : </span>
                                    @if($sale->payment_status == 1)
                                    <span>{{__('db.Packing')}}</span>
                                    @elseif($sale->payment_status == 2)
                                    <span>{{__('db.Delivering')}}</span>
                                    @elseif($sale->payment_status == 3)
                                    <span>{{__('db.Delivered')}}</span>
                                    @endif
                                    @else
                                    <span class="mute">{{__('db.Shipping Status')}} : </span>
                                    <span>{{__('db.Pending Confirmation')}}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <span class="mute">{{__('db.name')}} : </span>
                                    <span>{{ $sale->shipping_name }}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="mute">{{__('db.Phone')}} : </span>
                                    <span>{{ $sale->shipping_phone }}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="mute">{{__('db.Address')}} : </span>
                                    <span>{{ $sale->shipping_address }}</span>
                                    @if(strlen($sale->shipping_city) > 0)
                                    <span>, {{ $sale->shipping_city }}</span>
                                    @endif
                                    @if(strlen($sale->shipping_state) > 0)
                                    <span>, {{ $sale->shipping_state }}</span>
                                    @endif
                                    @if(strlen($sale->shipping_country) > 0)
                                    <span>, {{ $sale->shipping_country }}</span>
                                    @endif
                                    @if(strlen($sale->shipping_zip) > 0)
                                    <span>, {{ $sale->shipping_zip }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr class="mt-3 mb-3">
                        <div class="cart-table table-content table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(!empty($sale))
                                    @foreach($product_sales as $product)
                                    <tr class="sale-{{$sale->id}}">
                                        <td class="d-flex" style="text-align: left;">
                                            @if($product->image!==null)
                                            @php
                                                $images = explode(',', $product->image);
                                                $product->image = $images[0];
                                            @endphp
                                            <img loading="lazy" class="product-img" data-src="{{ url('images/product/small/') }}/{{ $product->image }}" alt="{{ $product->name }}">
                                            @else
                                            <img loading="lazy" class="product-img" src="https://dummyimage.com/50x50/e5e8ec/e5e8ec&text={{ $product->name }}" alt="{{ $product->name }}">
                                            @endif
                                            {!! $product->name !!}
                                            @if($product->is_variant == 1)
                                            @php
                                                $variant = \App\Models\Variant::where('id',$product->variant_id)->first();
                                                $variant_name = explode(',',str_replace('/',',',$variant->name));
                                                $product->variant_option = json_decode($product->variant_option);
                                                $count_variant = count($product->variant_option);
                                            @endphp

                                            (@foreach($product->variant_option as $key=>$option)
                                            {{$option}} : {{$variant_name[$key]}} @if($count_variant != ($key+1)) , @endif
                                            @endforeach)
                                            @endif
                                        </td>
                                        <td>
                                            {{ $product->qty }}
                                        </td>
                                        <td>
                                            {{ $product->net_unit_price }} 
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                                <tfooter>
                                    <tr>
                                        <td></td>
                                        <td><span class="mute">{{__('db.Shipping')}}</span></td>
                                        <th>(+) 
                                            @if($general_setting->currency_position == 'prefix')
                                            {{$currency->symbol ?? $currency->code}} {{ $sale->shipping_cost ?? '0' }}
                                            @else
                                            {{ $sale->shipping_cost ?? '0' }} {{$currency->symbol ?? $currency->code}}
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td><span class="mute">{{__('db.Discount')}}</span></td>
                                        <th>(-) 
                                            @if($general_setting->currency_position == 'prefix')
                                            {{$currency->symbol ?? $currency->code}} {{ $sale->coupon_discount ?? '0' }}
                                            @else
                                            {{ $sale->coupon_discount ?? '0' }} {{$currency->symbol ?? $currency->code}}
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th><h4>{{__('db.Total')}}</h4></th>
                                        <th>
                                            <h4>
                                            @if($general_setting->currency_position == 'prefix')
                                            {{$currency->symbol ?? $currency->code}} {{ ($sale->grand_total - $sale->coupon_discount) }}
                                            @else
                                            {{ ($sale->grand_total - $sale->coupon_discount) }} {{$currency->symbol ?? $currency->code}}
                                            @endif
                                            </h4>
                                        </th>
                                    </tr>
                                </tfooter>
                            </table>
                        </div>
                    </div>
                </div>
                <a href="{{url('track-order')}}" class="button lg style1 d-block text-center mt-5">{{__('db.Track another order')}}</a>
                @else
                <form id="track-order" action="{{ url('track-order') }}" method="get">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="order_id" id="order_id" tabindex="1" class="form-control" placeholder="Order ID" value="" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="email" id="email" tabindex="1" class="form-control" placeholder="Email" value="" required>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-6 col-sm-12">
                                <div class="res-box">
                                    <button type="submit" tabindex="4" class="button style1 d-block">{{__('db.Track')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</section>
<!--Shop cart ends-->
@endsection

@section('script')
<script type="text/javascript">
    "use strict";

    $('#track-order').on('submit', function(e) {
        e.preventDefault();

        var order_id = $('#order_id').val();
        var email = $('#email').val();
        var route = "{{ url('/track-order') }}/" + order_id + "/" + email;

        window.location.href = route;
    })
</script>
@endsection