@php
    $sliders = explode(',', $widget->slider_images);
    $links = explode(',', $widget->slider_links)
@endphp
<section class="mb-3">
    <div class="container-fluid">
        <div id="widget-slider" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                @foreach($sliders as $key=>$slider)
                <li data-target="#widget-slider" data-slide-to="{{$key}}" class="@if($key == 0) active @endif"></li>
                @endforeach
            </ol>

            <div class="carousel-inner">
                @foreach($sliders as $key=>$slider)
                <a class="carousel-item @if($key == 0) active @endif" href="@if(isset($links[$key])){{$links[$key]}}@endif">
                    <div class="single-carousel-item">
                    @php
                        $file = public_path('frontend/images/slider_widget/mobile/' . $slider);
                    @endphp

                    @if(file_exists($file))
                        <img data-src-m="{{ url('frontend/images/slider_widget/mobile/' . $slider) }}" src="{{ url('frontend/images/slider_widget/desktop/' . $slider) }}" alt="" />
                    @else
                        <img data-src-m="" src="{{ url('frontend/images/slider_widget/desktop/' . $slider) }}" alt="" />
                    @endif

                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div> 
</section>