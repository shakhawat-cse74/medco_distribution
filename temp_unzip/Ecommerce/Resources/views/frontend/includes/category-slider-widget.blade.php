@php
        if(Cache::has('category_list')){
            $parent_categories = Cache::get('category_list')->whereIn('id',explode(',',$widget->category_slider_ids));  
        }else{
            $parent_categories = DB::table('categories')->where('is_active', true)->whereIn('id',explode(',',$widget->category_slider_ids))->get();
        }
    @endphp
    <section class="category-tab-section mb-3">
        <div class="container-fluid" style="overflow-x:hidden">
            <div class="section-title mb-3">
                <div class="d-flex align-items-center">
                    <h3>{{$widget->category_slider_title}}</h3>
                </div>
                @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme != 'fashion')
                <div class="category-navigation">
                    <div class="category-button-prev"><span class="material-symbols-outlined">chevron_left</span></div>
                    <div class="category-button-next"><span class="material-symbols-outlined">chevron_right</span></div>
                </div>
                @endif
            </div>

            <div class="category-slider-wrapper swiper-container" data-loop="{{$widget->category_slider_loop}}" data-autoplay="{{$widget->category_slider_autoplay}}">
                <div class="swiper-wrapper">
                    @forelse ($parent_categories as $category)
                        <div class="swiper-slide">
                            <a href="{{url('shop')}}/{{$category->slug}}">
                                <div class="category-container">
                                    @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'fashion')
                                        @if($category->image!==null)
                                            <img loading="lazy" class="category-img" data-src="{{ url('images/category/large') }}/{{ $category->image }}" alt="{{ $category->name }}">
                                        @else
                                            <img loading="lazy" src="https://dummyimage.com/100x100/e5e8ec/e5e8ec&text={{ $category->name }}" alt="{{ $category->name }}">
                                        @endif
                                    @else
                                        @if($category->icon!==null)
                                            <img loading="lazy" class="category-img" data-src="{{ url('images/category/icons/') }}/{{ $category->icon }}" alt="{{ $category->name }}">
                                        @else
                                            <img loading="lazy" src="https://dummyimage.com/100x100/e5e8ec/e5e8ec&text={{ $category->name }}" alt="{{ $category->name }}">
                                        @endif
                                    @endif

                                    <div class="category-name">
                                    {{ ucwords($category->name) }}
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                    @endforelse
                </div>
                @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'fashion')
                <div class="category-navigation">
                    <div class="category-button-prev"><span class="material-symbols-outlined">chevron_left</span></div>
                    <div class="category-button-next"><span class="material-symbols-outlined">chevron_right</span></div>
                </div>
                @endif
            </div>
        </div>
    </section>
    
    