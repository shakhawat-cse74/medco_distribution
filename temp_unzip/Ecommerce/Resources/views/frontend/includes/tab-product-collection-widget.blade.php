    @php
    $collections = DB::table('collections')->whereIn('id',explode(',',$widget->tab_product_collection_id))->get();
    @endphp
    <!--Product area starts-->
    <section class="product-tab-section">
        <div class="container-fluid">
            <div class="section-title mb-3">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                @foreach($collections as $key=>$collection)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($key == 0) active @endif" id="{{$collection->slug}}-tab" data-toggle="tab" data-target="#{{$collection->slug}}" type="button" role="tab" aria-controls="{{$collection->slug}}" aria-selected="true">{{$collection->name}}</button>
                    </li>
                @endforeach
                </ul>
                @if($widget->tab_product_collection_type == 'slider')
                <div class="product-navigation">
                    <div class="product-button-next v1"><span class="material-symbols-outlined">chevron_right</span></div>
                    <div class="product-button-prev v1"><span class="material-symbols-outlined">chevron_left</span></div>
                </div>
                @endif
            </div> 
            <div class="tab-content" id="myTabContent">
                @foreach($collections as $key=>$collection)
                @php
                $product_arr = explode(',',$collection->products);
                $products = DB::table('products')->where('is_active', true)->where('is_online', true)->whereIn('id',$product_arr)->offset(0)->limit($widget->tab_product_collection_limit)->get();
                @endphp
                <div class="tab-pane fade @if($key == 0) show active @endif" id="{{$collection->slug}}" role="tabpanel" aria-labelledby="{{$collection->slug}}-tab">
                    @if($widget->tab_product_collection_type == 'slider')
                    <div class="product-slider-wrapper swiper-container" data-loop="{{$widget->tab_product_collection_slider_loop}}" data-autoplay="{{$widget->tab_product_collection_slider_autoplay}}">
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
                @endforeach
            </div>
        </div>
    </section>
    <!--product area ends-->