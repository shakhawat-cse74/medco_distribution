@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <!-- <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">LogIn</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li class="active">Login</li>
                    </ul>
                </div>
            </div>
        </div>
    </div> -->
    <!--Breadcrumb Area ends-->
    <!--Shop cart starts-->
    <section class="user-login-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <form action="{{url('/verifyId')}}" method="post" class="form-signin">
                    @csrf
                        <h3 class="h4 mb-3 font-weight-normal">Verify OTP</h3>
                        <p>Type in the code which we have sent to your mobile.</p>
                        <div class="form-group">
                            <input style="letter-spacing: 30px;text-align: center;font-size: 30px" type="text" min="6" maxlength="6" name="otp" class="form-control" required autofocus>
                            <input type="hidden" name="pass" value="@if(isset($pass)){{$pass}}@endif">
                            <input type="hidden" name="id" value="{{$id ?? ''}}">
                            <input type="hidden" name="phone" value="{{$phone ?? ''}}">
                        </div>
                        <button class="button style1 d-block" type="submit">Verify</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!--Shop cart ends-->
@endsection

@section('script')

@endsection