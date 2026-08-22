    @php
        $products = DB::table('products')->where('is_active', true)->where('is_online', true)->where('category_id',$widget->product_category_id)->offset(0)->limit($widget->product_category_limit)->get();
    @endphp

    <section class="product-tab-section">
        <div class="container-fluid">
            <div class="section-title mb-3">
                <div class="d-flex align-items-center">
                    <h3>{{$widget->product_category_title}}</h3>
                </div>
                @if($widget->product_category_type == 'slider')
                <div class="product-navigation">
                    <div class="product-button-next v1"><span class="material-symbols-outlined">chevron_right</span></div>
                    <div class="product-button-prev v1"><span class="material-symbols-outlined">chevron_left</span></div>
                </div>
                @endif
            </div> 

            @if($widget->product_category_type == 'slider')
            <div class="product-slider-wrapper swiper-container" data-loop="{{$widget->category_slider_loop}}" data-autoplay="{{$widget->category_slider_autoplay}}">
                <div class="swiper-wrapper">
                    @forelse ($products as $product)
                    <div class="swiper-slide">
                    @include('ecommerce::frontend.includes.product-template')
                    </div>
                    @empty
                    @endforelse
                </div>
            </div>
            @else
            <div class="product-grid">
                @foreach($products as $product)
                @include('ecommerce::frontend.includes.product-template')
                @endforeach
            </div>
            @endif
        </div>
    </section>
