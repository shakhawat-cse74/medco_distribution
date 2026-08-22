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
                            <form id="login-form" action="{{ route('checkEmail') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <input type="text" name="email" id="email" tabindex="1" class="form-control" placeholder="Email" value="" required>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 col-sm-12">
                                            <div class="res-box">
                                                <button type="submit" id="login-submit" tabindex="4" class="button style1 d-block">{{__('db.Verify')}}</button>
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

@endsection