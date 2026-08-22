@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">{{__('db.Shop Collections')}}</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li class="active">{{__('db.Shop Collections')}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Shop cart starts-->
    <section class="shop-cart-section">
        <div class="container-fluid">
            <div class="mb-5">
                <a href="{{url('products/collections/all')}}" class="collection-name">All</a>
                @foreach($collections as $collection)
                <a href="{{url('products')}}/{{$collection->slug}}" class="collection-name">{{$collection->name}} ({{count(explode(',', $collection->products))}})</a>
                @endforeach
            </div>
            <div class="product-grid">
                @foreach($products as $product)
                @include('ecommerce::frontend.includes.product-template')
                @endforeach
                @if(count($products) == 0)
                <h3 class="text-center mt-5 mb-5 d-block w-100">Sorry, no products found</h3>
                @endif
            </div>
        </div>
    </section>
    <!--Shop cart ends-->
@endsection

@section('script')

@endsection