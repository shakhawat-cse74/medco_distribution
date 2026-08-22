@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
    <!--Shop cart starts-->
    <section class="user-login-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="ui-dash tab-content mar-bot-30">
                        <div class="tab-pane fade show active" id="login" role="tabpanel">
                            <form id="login-form" action="{{ route('changePass') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <input type="password" name="password" id="password" tabindex="1" class="form-control" placeholder="password" value="" required>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="confirm-password" id="confirm_password" tabindex="1" class="form-control" placeholder="confirm password" value="" required>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 col-sm-12">
                                            <div class="res-box">
                                                <input type="hidden" name="id" value="{{$id}}">
                                                <button type="submit" id="login-submit" tabindex="4" class="button style1 d-block">Reset Password</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Shop cart ends-->
@endsection

@section('script')
    <script>
        $('#confirm_password').on('keyup', function () {
            if ($('#password').val() == $('#confirm_password').val()) {
                $('.alert').removeClass('alert-danger').addClass('alert-success show');
                $('.alert .message').html('Perfect!');
            } else {
                $('.alert').addClass('alert-danger show');
                $('.alert .message').html('Your passwords do not match!');
            }
        });
    </script>

@endsection