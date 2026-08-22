@extends('ecommerce::frontend.layout.main')

@section('title') @if($category->page_title) {{$category->page_title}} @else {{ $ecommerce_setting->site_title ?? '' }} @endif @endsection

@section('description') @if($category->short_description) {{$category->short_description}} @else  @endif @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">{{ $category->name }}</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li class="active">{{ $category->name }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Shop cart starts-->
    <section class="shop-cart-section">
        <div class="container-fluid">
            <div class="product-grid">
                @foreach($sub_categories as $sub_category)
                <a href="{{ url('shop') }}/{{ $sub_category->slug }}">
                    <div class="single-product-wrapper">
                        <div class="single-product-item">
                            <img src="{{ url('images/category/') }}/{{ $sub_category->image }}" alt="{{ $sub_category->name }}">
                        </div>
                        <div class="product-details">
                            <h3 class="product-name">
                                {{ $sub_category->name }}
                            </h3>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    <!--Shop cart ends-->
@endsection

@section('script')

@endsection