@extends('ecommerce::frontend.layout.main')

@section('title') {{__('db.Cart')}} {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">{{__('db.Shop Cart')}}</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">{{__('db.Home')}}</a></li>
                        <li class="active">{{__('db.Shop Cart')}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Shop cart starts-->
    <section class="shop-cart-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12 cart-table-container">
                    @if(session('total_qty') > 0)
                    <div class="cart-table table-content table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="cart-product-name">{{__('db.product')}}</th>
                                    <th class="cart-product-quantity">{{__('db.Price')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                            	@foreach($cart as $id => $cart_product)
                                @php
                                    if($cart_product['variant'] != 0){
                                        $variant = implode(' | ', $cart_product['variant']);
                                        $true_variant = implode(',', $cart_product['variant']);
                                        $id = $cart_product['id'].'-'.implode('-', $cart_product['variant']);
                                    }else{
                                        $true_variant = 0;
                                        $variant = 0;
                                        $id = $cart_product['id'];
                                    }
                                @endphp
                                <tr class="single-cart-item-{{$id}}">
                                    <td class="cart-product-name">
                                        <div class="d-flex align-self-center">
                                            <div class="remove">
                                                <a class="remove-from-cart cart" title="Remove from Cart" data-id="{{$cart_product['id']}}" data-variant="{{$true_variant}}">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </a>
                                            </div>
                                            <div>
                                                @if($cart_product['image']!==null)
                                                    @php
                                                        $images = explode(',', $cart_product['image']);
                                                        $cart_product['image'] = $images[0];
                                                    @endphp
                                                @endif
                                                <img src="{{ url('images/product/small') }}/{{ $cart_product['image'] }}" alt="{{ $cart_product['name'] }}">
                                            </div>
                                            <span class="align-self-center">{!! $cart_product['name'] !!} @if($variant != 0)({{$variant}})@endif</span>
                                        </div>
                                    </td>
                                    <td class="cart-product-quantity">
                                        <div class="product-subtotal">
                                            @if($general_setting->currency_position == 'prefix')
                                            <span class="amount" data-price="{{ $cart_product['total_price'] }}">{{$currency->symbol ?? $currency->code}} {{ $cart_product['total_price'] *  ($currency->exchange_rate ?? 1) }}</span>
                                            @else
                                            <span class="amount" data-price="{{ $cart_product['total_price'] }}">{{ $cart_product['total_price']  }} {{$currency->symbol ?? $currency->code}}</span>
                                            @endif
                                        </div>
                                        <div class="input-qty">
                                            <span class="input-group-btn">
                                                <button type="button" class="cart-quantity-left-minus" data-id="{{$cart_product['id']}}" data-variant="{{$true_variant}}">
                                                    <i class="material-symbols-outlined">remove</i>
                                                </button>
                                            </span>
                                            <input type="text" class="input-number" value="{{ $cart_product['qty'] }}">
                                            <span class="input-group-btn">
                                                <button type="button" class="cart-quantity-right-plus" data-id="{{$cart_product['id']}}" data-variant="{{$true_variant}}">
                                                    <i class="material-symbols-outlined">add</i>
                                                </button>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <hr>
                        <h4 class="text-right mt-3 mb-4">
                            {{__('db.Sub Total')}}:
                            @if($general_setting->currency_position == 'prefix')
                            <span class="currency_code">{{$currency->symbol ?? $currency->code}}</span><span class="sub_total" data-subtotal="{{  ($subTotal ?? 0.00 ) }}">{{ ($subTotal ?? 0.00) * $currency->exchange_rate }}</span>
                            @else
                            <span class="sub_total" data-subtotal="{{  ($subTotal ?? 0.00 ) }}">{{ ($subTotal ?? 0.00 ) * $currency->exchange_rate }}</span><span class="currency_code">{{$currency->symbol ?? $currency->code}}</span>
                            @endif
                        </h4>
                    </div>
                    <div class="row mb-5">
                        <div class="col-md-6 col-sm-6 col-12 mt-4 text-center">
                            <a href="{{ url('shop') }}" class="button style3">{{__('db.Continue Shopping')}}</a>
                        </div>
                        <div class="col-md-6 col-sm-6 col-12 mt-4 text-center">
                            <a href="{{ url('checkout') }}" class="button style1">{{__('db.Proceed to Checkout')}}</a>
                        </div>
                    </div>
                    @else
                    <div class="text-center">
                        <h3>{{__('db.No item in your cart')}}</h3>
                        <a href="{{ url('shop') }}" class="button style3">{{__('db.Continue Shopping')}}</a>
                    </div>
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

		$(document).on('click', '.remove-from-cart.cart', function(e){
            e.preventDefault();
            var id = $(this).data('id');
            var variant = $(this).data('variant');

            var route = "{{ route('removeFromCart') }}";

            $.ajax({
                url: route,
                type:"POST",
                data:{
                    product_id: id,
                    variant: variant,
                },
                success:function(response){
                    console.log(response);
                    if(response) {
                        $('.alert').addClass('alert-custom show');
                        $('.alert-custom .message').html(response.success);
                        $('.single-cart-item-'+response.deleted_item).html('').css('padding', 0);
                        $('.sub_total').html(response.subTotal.toFixed(2));
                        $('#cart-item-'+response.deleted_item).html('').css('padding', 0);
                        $('.cart__menu .cart_qty').html(response.total_qty);
                        $('.cart__menu .total').html(formatCurrency(response.subTotal));

                        if(response.total_qty < 1) {
                            $('.sub_total').html(0);
                            $('.shp__cart__wrap').html('<h6 class="mar-top-30">No item in your cart</h6>');
                            $('.cart__menu .cart_qty').html('0');
                            $('.cart__menu .total').html(formatCurrency(0));
                            $('.cart-table-container').html('<div class="text-center"><h3>{{trans("file.No item in your cart")}}</h3><a href="{{ url("shop") }}" class="button style3">{{trans("file.Continue Shopping")}}</a></div>');
                        }
                    }
                },
            });
        })

        var qty = 0;
        $('.cart-quantity-right-plus').on("click", function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var variant = $(this).data('variant');
            var qty = parseInt($(this).parent().siblings("input.input-number").val());
            var price = (parseFloat($(this).parent().parent().siblings(".product-subtotal").children(".amount").html()) / qty);
            $(this).parent().siblings("input.input-number").val(qty + 1);
            var qty = parseInt($(this).parent().siblings("input.input-number").val());
            price = (price * qty);
            $(this).parent().parent().siblings(".product-subtotal").children(".amount").html(price.toFixed(2));

            var sub_total = 0;
            // iterate through each td based on class and add the values
            $(".product-subtotal .amount").each(function() {
                var value = parseFloat($(this).html());
                // add only if the value is number
                if(!isNaN(value) && value.length != 0) {
                    sub_total += parseFloat(value);
                }
            });

            $('.sub_total').html(sub_total.toFixed(2));

            var route = "{{ route('updateCart') }}";

            $.ajax({
                url: route,
                type:"POST",
                data:{
                    product_id: id,
                    product_qty: qty,
                    product_variant: variant,
                },
                success:function(response){
                    console.log(response);
                    $('.cart__menu .cart_qty').html(response.total_qty);
                    $('.cart__menu .total').html(formatCurrency(response.subTotal));
                },
            });

        });
        $('.cart-quantity-left-minus').on("click", function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var variant = $(this).data('variant');
            var qty = parseInt($(this).parent().siblings("input.input-number").val());
            var price = (parseFloat($(this).parent().parent().siblings(".product-subtotal").children(".amount").html()) / qty);
            if (qty > 1) {
                $(this).parent().siblings("input.input-number").val(qty - 1);
                var qty = parseInt($(this).parent().siblings("input.input-number").val());
                price = (price * qty);
                $(this).parent().parent().siblings(".product-subtotal").children(".amount").html(price.toFixed(2));

                var sub_total = 0;
                // iterate through each td based on class and add the values
                $(".product-subtotal .amount").each(function() {
                    var value = parseFloat($(this).html());
                    // add only if the value is number
                    if(!isNaN(value) && value.length != 0) {
                        sub_total += parseFloat(value);
                    }
                });

                $('.sub_total').html(sub_total.toFixed(2));

                var route = "{{ route('updateCart') }}";

                $.ajax({
                    url: route,
                    type:"POST",
                    data:{
                        product_id: id,
                        product_qty: qty,
                        product_variant: variant,
                    },
                    success:function(response){
                        console.log(response);
                        $('.cart__menu .cart_qty').html(response.total_qty);
                        $('.cart__menu .total').html(formatCurrency(response.subTotal));
                    },
                });
            }
        });
	</script>
@endsection
