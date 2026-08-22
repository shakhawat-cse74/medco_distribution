@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description') {{ '' }} @endsection

@section('content')

@if(isset($sliders))
<!--Home Banner starts -->
<section class="banner-area v3 pt-0">
    @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme != 'fashion')
    <div class="container-fluid">
    @endif

        <div class="single-banner-item">
            <div class="row">
                @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'default')
                <div class="col-md-9 offset-md-3">
                @else
                <div class="col-md-12">
                @endif
                    <div id="hero-slider" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner">
                            @foreach($sliders as $key=>$slider)
                            <a class="carousel-item @if($key == 0) active @endif" href="{{$slider->link}}">
                                <div class="single-carousel-item">
                                    <img data-src-m="@if(!empty($slider->image3)){{ url('frontend/images/slider/mobile/') }}/{{$slider->image3}}@endif" src="{{ url('frontend/images/slider/desktop/') }}/{{$slider->image1}}" alt="" />
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @if(count($sliders) > 1)
                        <button class="carousel-control-prev" type="button" data-target="#hero-slider" data-slide="prev">
                            <span aria-hidden="true"><i class="material-symbols-outlined">chevron_left</i></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-target="#hero-slider" data-slide="next">
                            <span aria-hidden="true"><i class="material-symbols-outlined">chevron_right</i></span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme != 'fashion')
    </div>
    @endif
</section>
<!--Home Banner Area ends-->
@endif

@if(isset($widgets))
@foreach($widgets as $widget)
@if($widget->name == 'category-slider-widget')
@include('ecommerce::frontend.includes.category-slider-widget')
@endif

@if($widget->name == 'brand-slider-widget')
@include('ecommerce::frontend.includes.brand-slider-widget')
@endif

@if($widget->name == 'product-category-widget')
@include('ecommerce::frontend.includes.product-category-widget')
@endif

@if($widget->name == 'product-collection-widget')
@include('ecommerce::frontend.includes.product-collection-widget')
@endif

@if($widget->name == 'text-widget')
@include('ecommerce::frontend.includes.text-widget')
@endif

@if($widget->name == 'three-c-banner-widget')
@include('ecommerce::frontend.includes.three-c-banner-widget')
@endif

@if($widget->name == 'two-c-banner-widget')
@include('ecommerce::frontend.includes.two-c-banner-widget')
@endif

@if($widget->name == 'one-c-banner-widget')
@include('ecommerce::frontend.includes.one-c-banner-widget')
@endif

@if($widget->name == 'tab-product-category-widget')
@include('ecommerce::frontend.includes.tab-product-category-widget')
@endif

@if($widget->name == 'tab-product-collection-widget')
@include('ecommerce::frontend.includes.tab-product-collection-widget')
@endif

@if($widget->name == 'image-slider-widget')
@include('ecommerce::frontend.includes.image-slider-widget')
@endif
@endforeach
@endif

@if(isset($recently_viewed) && count($recently_viewed) > 0)
@include('ecommerce::frontend.includes.recently-viewed-products')
@endif

@endsection

@section('script')
<script>
    {!! file_get_contents(Module::find('Ecommerce')->getPath(). "/assets/js/swiper.min.js") !!}
</script>
<script type="text/javascript">
    "use strict";

    //category carousel
    if ($('.category-slider-wrapper').length > 0) {
        var swiper = new Swiper('.category-slider-wrapper', {
            @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'fashion')
            slidesPerView: 3,
            centeredSlides: true,
            @else
            slidesPerView: 6,
            @endif
            spaceBetween: 30,
            lazy: true,
            loop: true,
            navigation: {
                nextEl: '.category-button-next',
                prevEl: '.category-button-prev',
            },
            autoplay: {
                delay: 4000,
            },
            // Responsive breakpoints
            breakpoints: {
                // when window width is <= 675
                @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'fashion')
                675: {
                    slidesPerView: 1,
                },
                @else
                675: {
                    slidesPerView: 2,
                    spaceBetween: 30
                },
                @endif

                // when window width is <= 991
                991: {
                    slidesPerView: 4,
                    spaceBetween: 30
                },
                // when window width is <= 1024px
                1024: {
                    @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'fashion')
                    slidesPerView: 4,
                    @else
                    slidesPerView: 6,
                    @endif
                    spaceBetween: 15
                }
            }
        });
    }

    $(document).ready(function(){
        $('.category-img').each(function(){
            var img = $(this).data('src');
            $(this).attr('src', img);
        })

        $('.banner-img').each(function(){
            var img = $(this).data('src');
            $(this).attr('src', img);
        })
    })

    //product carousel
    if ($('.product-slider-wrapper').length > 0) {
        var swiper = new Swiper('.product-slider-wrapper', {
            @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'fashion')
            slidesPerView: 4,
            @else
            slidesPerView: 5,
            @endif
            spaceBetween: 0,
            lazy: true,
            observer: true,
            observeParents: true,
            loop: false,
            navigation: {
                nextEl: '.product-button-next',
                prevEl: '.product-button-prev',
            },
            autoplay: {
                delay: 4000,
            },
            // Responsive breakpoints
            breakpoints: {
                // when window width is <= 675
                @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'fashion')
                675: {
                    slidesPerView: 1,
                },
                @else
                675: {
                    slidesPerView: 2,
                    spaceBetween: 30
                },
                @endif

                // when window width is <= 991
                991: {
                    slidesPerView: 4,
                    spaceBetween: 30
                },
                // when window width is <= 1024px
                1024: {
                    slidesPerView: 6,
                    spaceBetween: 15
                }
            }
        });
    }

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
                console.log(response)
                if (response) {
                    $('.alert').addClass('alert-custom show');
                    $('.alert-custom .message').html(response.success);
                    $('.cart__menu .cart_qty').html(response.total_qty);

                    $('.cart__menu .total').html(response.currency_code + (response.subTotal * response.currency_rate).toFixed(2));
                    // $('.cart__menu .total').html(formatCurrency(response.subTotal));
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
