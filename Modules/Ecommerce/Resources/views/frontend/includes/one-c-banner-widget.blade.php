<section class="mb-3">
    @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'fashion')
    <div class="row">
        <div class="col-md-12">
            <a href="{{$widget->one_c_banner_link1}}">
                @php
                    $file = public_path('frontend/images/banners/mobile/' . $widget->one_c_banner_image1);
                @endphp

                @if(file_exists($file))
                    <img class="banner-img" loading="lazy" data-src-m="{{ url('frontend/images/banners/mobile/' . $widget->one_c_banner_image1) }}" src="{{ url('frontend/images/banners/desktop/' . $widget->one_c_banner_image1) }}" alt="" />
                @else
                    <img class="banner-img" loading="lazy" data-src-m="" src="{{ url('frontend/images/banners/desktop/' . $widget->one_c_banner_image1) }}" alt="" />
                @endif
            </a>
        </div>
    </div>
    @else
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <a href="{{$widget->one_c_banner_link1}}">
                    @php
                        $file = public_path('frontend/images/banners/mobile/' . $widget->one_c_banner_image1);
                    @endphp

                    @if(file_exists($file))
                        <img class="banner-img" loading="lazy" data-src-m="{{ url('frontend/images/banners/mobile/' . $widget->one_c_banner_image1) }}" src="{{ url('frontend/images/banners/desktop/' . $widget->one_c_banner_image1) }}" alt="" />
                    @else
                        <img class="banner-img" loading="lazy" data-src-m="" src="{{ url('frontend/images/banners/desktop/' . $widget->one_c_banner_image1) }}" alt="" />
                    @endif         
                </a>
            </div>
        </div>
    </div>
    @endif
</section>