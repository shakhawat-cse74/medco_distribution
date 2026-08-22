    @php
        $collection = DB::table('collections')->where('id',$widget->product_collection_id)->first();
        if($collection){
            $product_arr = explode(',',$collection->products);
        }
        if(isset($product_arr)){
            $products = DB::table('products')->where('is_active', true)->where('is_online', true)->whereIn('id',$product_arr)->offset(0)->limit($widget->product_collection_limit)->get();
        }
    @endphp
    @if(isset($products) && count($products) > 0)
    <section class="product-tab-section">
        <div class="container-fluid">
            <div class="section-title mb-3">
                <div class="d-flex align-items-center">
                    <h3>{{$widget->product_collection_title}}</h3>
                </div>
                @if($widget->product_collection_type == 'slider')
                <div class="product-navigation">
                    <div class="product-button-next v1"><span class="material-symbols-outlined">chevron_right</span></div>
                    <div class="product-button-prev v1"><span class="material-symbols-outlined">chevron_left</span></div>
                </div>
                @endif
            </div> 

            @if($widget->product_collection_type == 'slider')
            <div class="product-slider-wrapper swiper-container" data-loop="{{$widget->product_collection_slider_loop}}" data-autoplay="{{$widget->product_collection_slider_autoplay}}">
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
    @endif
