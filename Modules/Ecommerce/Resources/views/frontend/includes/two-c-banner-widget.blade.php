<section class="mb-3">
    <div class="container-fluid">
        <div class="row">
            @if(!empty($widget->two_c_banner_image1))
            <div class="col-md-6">
                <a href="{{$widget->two_c_banner_link1 ?? '#'}}"><img loading="lazy" class="banner-img" src="{{ url('frontend/images/banners/') }}/{{ $widget->two_c_banner_image1 }}" data-src="{{ url('frontend/images/banners/') }}/{{ $widget->two_c_banner_image1 }}" alt="banner" /></a>
            </div>
            @endif
            @if(!empty($widget->two_c_banner_image2))
            <div class="col-md-6">
                <a href="{{$widget->two_c_banner_link2 ?? '#'}}"><img loading="lazy" class="banner-img" src="{{ url('frontend/images/banners/') }}/{{ $widget->two_c_banner_image2 }}" data-src="{{ url('frontend/images/banners/') }}/{{ $widget->two_c_banner_image2 }}" alt="banner" /></a>
            </div>
            @endif
        </div>
    </div>
</section>