@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')

    <!--Section starts-->
    <section class="">
        <div class="col-md-6 offset-md-3 text-center mb-5">
            <div style="font-size: 60px;color:forestgreen;"><span class="material-symbols-outlined">verified</span></div>
            <h3 class="mt-3">{{__('db.Thank you for your order')}}</h3>
            <p class="lead">{{__('db.Here is your order reference no')}}- <span class="theme-color">{{$sale_reference}}</span>. {{__('db.You will receive an email with delivery details shortly')}}
        </div>
    </section>
    <!--Section ends-->
@endsection

@section('script')

@endsection