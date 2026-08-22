@extends('ecommerce::frontend.layout.main')

@section('title') {{$page->meta_title}} @endsection

@section('description') {{$page->meta_description}} . @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">{{ $page->page_name }}</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">{{__('db.Home')}}</a></li>
                        <li class="active">{{ $page->page_name }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Section starts-->
    <section class="pt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="mar-bot-30">
                        {!! $page->description !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Section ends-->
@endsection

@section('script')

@endsection