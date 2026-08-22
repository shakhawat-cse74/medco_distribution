@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description') @endsection

@push('css')
<style>
    .table tr{border-top:1px solid #ddd}
    .price,.old-price {font-weight:500}
</style>
@endpush

@section('content')
<!--Breadcrumb Area start-->
<div class="breadcrumb-section">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="page-title">{{__('db.My Wishlist')}}</h1>
                <ul>
                    <li><a href="{{url('customer/profile')}}">{{__('db.dashboard')}}</a></li>
                    <li class="active">{{__('db.My Wishlist')}}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Breadcrumb Area ends-->
<!--My account Dashboard starts-->
<section class="my-account-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="user-sidebar-menu">
                    @include('ecommerce::frontend.customer-menu')
                </div>
            </div>
            <div class="col-md-9 tabs style1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="user-dashboard-tab__content tab-content">
                            <div class="tab-pane fade show active mar-top-30" id="addresses" role="tabpanel">
                                @if(isset($products) && count($products) > 0)
                                <div class="cart-table table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{__('db.product')}}</th>
                                                <th class="d-none d-lg-flex d-xl-flex justify-content-center">{{__('db.Price')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($products as $product)
                                            <tr id="{{$product->id}}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <a class="remove-from-wishlist" data-id="{{$product->id}}" style="font-size:16px;opacity:0.5">
                                                            <span class="material-symbols-outlined">delete</span>
                                                        </a>
                                                        @if($product->image!==null)
                                                        @php
                                                            $images = explode(',', $product->image);
                                                            $product->image = $images[0];
                                                        @endphp
                                                        <a href="{{url('product')}}/{{$product->slug}}/{{$product->id}}" class="view-details ml-2 mr-2">
                                                            <img style="width:50px;" loading="lazy" class="product-img" data-src="{{ url('images/product/small/') }}/{{ $product->image }}" alt="{{ $product->name }}">
                                                        </a>
                                                        @else
                                                        <a href="{{url('product')}}/{{$product->slug}}/{{$product->id}}" class="view-details ml-2 mr-2">
                                                            <img loading="lazy" src="https://dummyimage.com/50x50/e5e8ec/e5e8ec&text={{ $product->name }}" alt="{{ $product->name }}">
                                                        </a>
                                                        @endif
                                                        <div>
                                                            <a href="{{url('product')}}/{{$product->slug}}/{{$product->id}}" class="product-name">
                                                            {{ $product->name }}
                                                            </a>
                                                            @if(isset($product->unit))
                                                            <span class="product-quantity">({{ $product->unit->unit_name }})</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="d-lg-none d-xl-none d-flex align-items-center justify-content-between">
                                                        @if(($product->promotion == 1) && (($product->last_date > date('Y-m-d')) || !isset($product->last_date)))
                                                        <div>
                                                            <span class="price mr-1" data-price="{{ $product->promotion_price }}">
                                                                @if($general_setting->currency_position == 'prefix')
                                                                {{$currency->symbol ?? $currency->code}} {{ $product->promotion_price }}
                                                                @else
                                                                {{ $product->promotion_price }} {{$currency->symbol ?? $currency->code}}
                                                                @endif
                                                            </span>
                                                            <span class="old-price mr-3" data-old_price="{{ $product->price }}">
                                                                @if($general_setting->currency_position == 'prefix')
                                                                {{$currency->symbol ?? $currency->code}} {{ $product->price }}
                                                                @else
                                                                {{ $product->price }} {{$currency->symbol ?? $currency->code}}
                                                                @endif
                                                            </span>
                                                        </div>
                                                        @else
                                                        <span class="price mr-3" data-price="{{ $product->price }}">
                                                            @if($general_setting->currency_position == 'prefix')
                                                            {{$currency->symbol ?? $currency->code}} {{ $product->price }}
                                                            @else
                                                            {{ $product->price }} {{$currency->symbol ?? $currency->code}}
                                                            @endif
                                                        </span>
                                                        @endif

                                                        @if($product->in_stock == 1)
                                                            @if(is_null($product->is_variant))
                                                            <form class="d-flex justify-content-between" method="post" id="add_to_cart_{{ $product->id }}">
                                                                @csrf
                                                                <input type="hidden" name="qty" class="input-number" value="1" min="1" max="{{ $product->qty }}">
                                                                <button data-id="{{ $product->id }}" type="submit" class="button sm style1 add-to-cart"><span class="material-symbols-outlined">shopping_bag</span></button>
                                                            </form>
                                                            @else
                                                            <div class="text-center">
                                                                <a href="{{url('/')}}/product/{{$product->slug}}/{{$product->id}}" class="button style1">{{__('db.Add to cart')}}</a>
                                                            </div>
                                                            @endif
                                                        @else
                                                        <span>{{__('db.Out of stock')}}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="d-none d-lg-flex d-xl-flex align-items-center justify-content-between">
                                                    @if(($product->promotion == 1) && (($product->last_date > date('Y-m-d')) || !isset($product->last_date)))
                                                    <span class="price" data-price="{{ $product->promotion_price }}">
                                                        @if($general_setting->currency_position == 'prefix')
                                                        {{$currency->symbol ?? $currency->code}} {{ $product->promotion_price }}
                                                        @else
                                                        {{ $product->promotion_price }} {{$currency->symbol ?? $currency->code}}
                                                        @endif
                                                    </span>
                                                    <span class="old-price" data-old_price="{{ $product->price }}">
                                                        @if($general_setting->currency_position == 'prefix')
                                                        {{$currency->symbol ?? $currency->code}} {{ $product->price }}
                                                        @else
                                                        {{ $product->price }} {{$currency->symbol ?? $currency->code}}
                                                        @endif
                                                    </span>
                                                    @else
                                                    <span class="price" data-price="{{ $product->price }}">
                                                        @if($general_setting->currency_position == 'prefix')
                                                        {{$currency->symbol ?? $currency->code}} {{ $product->price }}
                                                        @else
                                                        {{ $product->price }} {{$currency->symbol ?? $currency->code}}
                                                        @endif
                                                    </span>
                                                    @endif

                                                    @if($product->in_stock == 1)
                                                        @if(is_null($product->is_variant))
                                                        <form class="d-flex justify-content-between" method="post" id="add_to_cart_{{ $product->id }}">
                                                            @csrf
                                                            <input type="hidden" name="qty" class="input-number" value="1" min="1" max="{{ $product->qty }}">
                                                            <button data-id="{{ $product->id }}" type="submit" class="button sm style1 add-to-cart"><span class="material-symbols-outlined">shopping_bag</span></button>
                                                        </form>
                                                        @else
                                                        <div class="text-center">
                                                            <a href="{{url('/')}}/product/{{$product->slug}}/{{$product->id}}" class="button style1">{{__('db.Add to cart')}}</a>
                                                        </div>
                                                        @endif
                                                    @else
                                                    <span>{{__('db.Out of stock')}}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="card mb-5">
                                    <div class="card-body">
                                        <h3>{{__('db.You have not added anything to wishlist yet')}}</h3>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<!--My account Dashboard ends-->
@endsection

@section('script')
<script type="text/javascript">
    "use strict";

    $('.remove-from-wishlist').on('click', function() {
        var product_id = $(this).data('id');
        $('#'+product_id).html('');
        $.ajax({
            type: "get",
            url: "{{url('customer/wishlist/delete')}}/" + product_id,
            success: function(data) {
                $('.alert').addClass('alert-custom show');
                $('.alert-custom .message').html('{{trans("file.product removed from wishlist")}}');
                setTimeout(function() {
                    $('.alert').removeClass('show');
                }, 4000);
            }
        })
    });

    $(document).on('click', '.add-to-cart', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var parent = '#add_to_cart_' + id;

        var qty = $(parent + " input[name=qty]").val();

        var route = "{{ route('addToCart') }}";

        var btn = $(this);

        var btn_text = $(this).html();

        $(this).html('<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">...</span></span>');

        $.ajax({
            url: route,
            type: "POST",
            data: {
                product_id: id,
                qty: qty,
            },
            success: function(response) {
                if (response) {
                    $('.alert').addClass('alert-custom show');
                    $('.alert-custom .message').html(response.success);
                    $('.cart__menu .cart_qty').html(response.total_qty);
                    $('.cart__menu .total').html(formatCurrency(response.subTotal));
                    $(btn).html(btn_text);
                    setTimeout(function() {
                        $('.alert').removeClass('show');
                    }, 4000);
                }
            },
        });
    })
</script>
@endsection
