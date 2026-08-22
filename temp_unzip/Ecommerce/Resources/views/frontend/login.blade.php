@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
    <section class="user-login-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4 offset-md-4">
                    @if(isset($verify) && ($verify == 0))
                    <div class="alert alert-warning mb-3">
                        <p>{{__('db.We have sent you an email')}}. {{__('db.Just click on the link in that email to verify')}}.</p>
                    </div>
                    @elseif(isset($verify) && ($verify == 1))
                    <div class="alert alert-success mb-3">
                        <p>{{__('db.Thank you for verifying your email')}}.</p>
                    </div>
                    @endif
                    <form class="mt-5" id="login-form" action="{{ route('customerLogin') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <input type="email" name="email" id="email" tabindex="1" class="form-control" placeholder="{{__('db.Email')}}" value="" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="{{__('db.Password')}}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-6 text-left">
                                <div class="res-box">
                                    <div class="custom-control custom-checkbox text-center pl-0 mt-3 mb-3">
                                        <input type="checkbox" class="custom-control-input" id="remember">
                                        <label class="custom-control-label" for="remember">{{__('db.Remember Me')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-6 text-right">
                                <div class="res-box">
                                    <div class="mt-3 mb-3">
                                        <a href="{{url('/customer/forgot-password')}}" tabindex="5" class="forgot-password">{{__('db.Forgot Password')}}?</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6 col-sm-12">
                                    <div class="res-box">
                                        <button type="submit" id="login-submit" tabindex="4" class="button style1 d-block">{{__('db.Log In')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            {{trans("file.don't have an account")}}? <a href="{{url('customer/register')}}">{{__('db.sign up now')}}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')

@endsection
