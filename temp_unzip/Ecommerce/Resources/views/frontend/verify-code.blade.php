@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
    <section class="user-login-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <form action="{{route('getPass')}}" method="post" class="form-signin">
                    @csrf
                        <h3 class="h4 mb-3 font-weight-normal">Verify Code</h3>
                        <p>Type in the code which we have sent to your email.</p>
                        <div class="form-group">
                            <input style="letter-spacing: 30px;text-align: center;font-size: 30px" type="text" min="6" maxlength="6" name="code" class="form-control" required autofocus>
                            <input type="hidden" name="id" value="{{$user->id}}" />
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