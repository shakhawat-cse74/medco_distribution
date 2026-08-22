@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
    <!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">Shop</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li class="active">Shop</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Shop cart starts-->
    <section class="product-tab-section">
        <div class="container-fluid">
            @foreach($categories as $key=>$category)
            <div class="row"> 
                <div class="col-12">
                    <h3 class="mb-0 h4"><a class="d-flex align-items-center" href="{{url('/shop')}}/{{$category->slug}}">{{$category->name}} <span class="material-symbols-outlined ml-2">arrow_forward</span></a></h3>
                    <hr>
                </div>
            </div>
            <div class="product-grid mar-bot-30 row">
                @php
                    $cats = DB::table('categories')->where('is_active', 1)->where('parent_id', $category->id)->get();
                @endphp
                @foreach($cats as $cat)
                <div class="col-sm-2 text-center mt-3">
                    <a href="{{ url('/shop') }}/{{$cat->slug}}">
                        @if($cat->icon!==null)
                            <img loading="lazy" class="category-img" data-src="{{ url('images/category/icons/') }}/{{ $cat->icon }}" alt="{{ $cat->name }}">
                        @else
                            <img loading="lazy" src="https://dummyimage.com/100x100/e5e8ec/e5e8ec&text={{ $cat->name }}" alt="{{ $cat->name }}">
                        @endif
                        <h3 class="product-name mr-3 ml-3 mt-3 mb-3">
                            {{ $cat->name }}
                        </h3>
                    </a>
                </div>
                @endforeach
                @if(count($cats) < 1)
                <div class="col-sm-2 text-center mt-3">
                    <a href="{{ url('/shop') }}/{{$category->slug}}">
                        @if($category->icon!==null)
                            <img loading="lazy" class="category-img" data-src="{{ url('images/category/icons/') }}/{{ $category->icon }}" alt="{{ $category->name }}">
                        @else
                            <img loading="lazy" src="https://dummyimage.com/100x100/e5e8ec/e5e8ec&text={{ $category->name }}" alt="{{ $category->name }}">
                        @endif
                        <h3 class="product-name mr-3 ml-3 mt-3 mb-3">
                            {{ $category->name }}
                        </h3>
                    </a>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    <!--Shop cart ends-->
@endsection

@section('script')
<script type="text/javascript">
    "use strict";
    $(document).ready(function(){
        $('.category-img').each(function(){
            var img = $(this).data('src');
            $(this).attr('src', img);
        })
    })
</script>
@endsection