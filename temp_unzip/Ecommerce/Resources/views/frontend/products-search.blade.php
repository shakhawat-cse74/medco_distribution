@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
    <!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">You searched for '{{$search}}'</h3>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Shop cart starts-->
    <section class="shop-cart-section">
        <div class="container-fluid">
            <div class="row"> 
            	<div class="product-grid">
                	@foreach($products as $product)
                    @include('ecommerce::frontend.includes.product-template')
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <!--Shop cart ends-->
@endsection

@section('script')
	<script type="text/javascript">
		"use strict";

		$(document).on('click', '.add-to-cart', function(e){
			e.preventDefault();
            var id = $(this).data('id');
            var parent = '#add_to_cart_'+id;

			var qty = $(parent+" input[name=qty]").val();

			var route = "{{ route('addToCart') }}";

			$.ajax({
		        url: route,
		        type:"POST",
		        data:{
					product_id: id,
					qty: qty,
		        },
		        success:function(response){
			        console.log(response);
		            if(response) {
		            	$('.alert').addClass('alert-custom show');
			            $('.alert-custom .message').html(response.success);
			            $('.cart__menu .cart_qty').html(response.total_qty);
			            $('.cart__menu .total').html(formatCurrency(response.subTotal));

                        setTimeout(function() {
                            $('.alert').removeClass('show');
                        }, 4000);
		            }
		        },
		    });
		})
        var quantitiy = 0;
        $('.cart-quantity-right-plus').on("click", function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var quantity = parseInt($(this).parent().siblings("input.input-number").val());
            var price = (parseInt($(this).parent().parent().siblings(".product-subtotal").children(".amount").html()) / quantity);
            $(this).parent().siblings("input.input-number").val(quantity + 1);
            var quantity = parseInt($(this).parent().siblings("input.input-number").val());
            price = (price * quantity);
            $(this).parent().parent().siblings(".product-subtotal").children(".amount").html(price);

            var sub_total = 0;
            // iterate through each td based on class and add the values
            $(".product-subtotal .amount").each(function() {
                var value = parseInt($(this).html());
                // add only if the value is number
                if(!isNaN(value) && value.length != 0) {
                    sub_total += parseFloat(value);
                }
            });

            $('.sub_total').html(sub_total);

            var route = "{{ route('updateCart') }}";

            $.ajax({
                url: route,
                type:"POST",
                data:{
                    product_id: id,
                    product_qty: quantity,
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
            var quantity = parseInt($(this).parent().siblings("input.input-number").val());
            var price = (parseInt($(this).parent().parent().siblings(".product-subtotal").children(".amount").html()) / quantity);
            if (quantity > 1) {
                $(this).parent().siblings("input.input-number").val(quantity - 1);
                var quantity = parseInt($(this).parent().siblings("input.input-number").val());
                price = (price * quantity);
                $(this).parent().parent().siblings(".product-subtotal").children(".amount").html(price);

                var sub_total = 0;
                // iterate through each td based on class and add the values
                $(".product-subtotal .amount").each(function() {
                    var value = parseInt($(this).html());
                    // add only if the value is number
                    if(!isNaN(value) && value.length != 0) {
                        sub_total += parseFloat(value);
                    }
                });

                $('.sub_total').html(sub_total);

                var route = "{{ route('updateCart') }}";

                $.ajax({
                    url: route,
                    type:"POST",
                    data:{
                        product_id: id,
                        product_qty: quantity,
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