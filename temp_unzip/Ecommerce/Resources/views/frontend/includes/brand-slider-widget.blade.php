    @php
        $brands = DB::table('brands')->where('is_active', true)->whereIn('id',explode(',',$widget->brand_slider_ids))->get();
    @endphp
    <section class="category-tab-section mb-3">
        <div class="container-fluid" style="overflow-x:hidden">
            <div class="section-title mb-3">
                <div class="d-flex align-items-center">
                    <h3>{{$widget->brand_slider_title}}</h3>
                </div>
                <div class="category-navigation">
                    <div class="category-button-prev"><span class="material-symbols-outlined">chevron_left</span></div>
                    <div class="category-button-next"><span class="material-symbols-outlined">chevron_right</span></div>
                </div>
            </div>

            <div class="category-slider-wrapper swiper-container" data-loop="{{$widget->brand_slider_loop}}" data-autoplay="{{$widget->brand_slider_autoplay}}">
                <div class="swiper-wrapper">
                    @forelse ($brands as $brand)
                        <div class="swiper-slide">
                            <a href="{{url('brand')}}/{{$brand->slug}}">
                                <div class="brand-container">
                                    <div class="brand-img">
                                    @if($brand->image!==null)
                                        <img loading="lazy" class="category-img" data-src="{{ url('images/brand/') }}/{{ $brand->image }}" alt="{{ $brand->title }}">
                                    @else
                                        <img loading="lazy" src="https://dummyimage.com/100x100/e5e8ec/e5e8ec&text={{ $brand->title }}" alt="{{ $brand->title }}">
                                    @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>
    </section>
    
    