@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')

    <!--Section starts-->
    <section class="">
        <div class="col-md-6 offset-md-3 text-center mb-5">
            <div style="font-size: 60px;color:red;"><span class="material-symbols-outlined">cancel</span></div>
            <h3 class="mt-3">{{__('db.Sorry, your payment failed')}}</h3>
            <p class="lead">{{__('db.Go back to checkout page and try again')}}- <span><a href="{{url('checkout')}}" class="theme-color">Checkout</a></span>.
        </div>
    </section>
    <!--Section ends-->
@endsection

@section('script')

@endsection