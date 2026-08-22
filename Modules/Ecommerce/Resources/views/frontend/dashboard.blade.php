@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">{{__('db.dashboard')}}</h1>
                    <ul>
                        <li><a href="{{url('customer/profile')}}">{{__('db.dashboard')}}</a></li>
                        <li class="active">{{__('db.dashboard')}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--My account dashboard starts-->
    <section class="my-account-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="user-sidebar-menu">
                        @include('ecommerce::frontend.customer-menu')
                    </div>
                </div>
                <div class="col-md-9 tabs style1">
                    <div class="row">
                        <div class="col-md-12 mar-top-30">
                            <div class="user-dashboard-tab__content tab-content">
                                <div class="tab-pane fade show active mar-top-30" id="dashboard" role="tabpanel">
                                    <h5>Hello <strong>{{ auth()->user()->name }}</strong></h5>
                                    <p>From your account dashboard you can easily check &amp; view your <a href="{{ url('orders') }}">recent orders</a>, manage your <a href="{{ url('address') }}">shipping and billing addresses</a> and <a href="{{ url('account-details') }}">edit your password and account details</a>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!--My account dashboard ends-->
@endsection

@section('script')

@endsection