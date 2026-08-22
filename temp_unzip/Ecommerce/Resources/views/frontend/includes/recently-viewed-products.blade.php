    @php
        $products = DB::table('products')->where('is_active', true)->where('is_online', true)->whereIn('id',$recently_viewed)->offset(0)->limit(20)->get();
    @endphp

    <section class="product-tab-section">
        <div class="container-fluid">
            <div class="section-title mb-3">
                <div class="d-flex align-items-center">
                    <h3>{{__('db.Recently Viewed')}}</h3>
                </div>
                @if(count($products) > 5 && $ecommerce_setting->theme != 'fashion')
                <div class="product-navigation">
                    <div class="product-button-next v1"><span class="material-symbols-outlined">chevron_right</span></div>
                    <div class="product-button-prev v1"><span class="material-symbols-outlined">chevron_left</span></div>
                </div>
                @endif
            </div> 
            <div class="product-slider-wrapper swiper-container" data-loop="0">
                <div class="swiper-wrapper">
                    @forelse ($products as $product)
                    <div class="swiper-slide">
                    @include('ecommerce::frontend.includes.product-template')
                    </div>
                    @empty
                    @endforelse
                </div>
                @if(count($products) > 4 && $ecommerce_setting->theme == 'fashion')
                <div class="product-navigation">
                    <div class="product-button-next v1"><span class="material-symbols-outlined">chevron_right</span></div>
                    <div class="product-button-prev v1"><span class="material-symbols-outlined">chevron_left</span></div>
                </div>
                @endif
            </div>
        </div>
    </section>
