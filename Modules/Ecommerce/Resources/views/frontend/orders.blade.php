@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">{{__('db.My Orders')}}</h1>
                    <ul>
                        <li><a href="{{url('customer/profile')}}">{{__('db.dashboard')}}</a></li>
                        <li class="active">{{__('db.My Orders')}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--My account Dashboard starts-->
    <section class="my-account-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="user-sidebar-menu mb-5">
                        @include('ecommerce::frontend.customer-menu')
                    </div>
                </div>
                <div class="col-md-9 tabs style1">
                    <div class="row">
                        <div class="col-md-12">
                            @if(!empty($sales))
                                @foreach($sales as $sale)
                                @php
                                    $currency = \App\Models\Currency::where('id',$sale->currency_id)->first();
                                @endphp
                                <div class="card mb-5">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>Order ID- {{ $sale->reference_no }}</strong>
                                                <span class="d-block">{{ date('d-m-Y', strtotime($sale->created_at)) }}</span>
                                            </div>
                                            <div>
                                                @if($sale->sale_status == 1)
                                                <span class="badge badge-success">Complete</span>
                                                @elseif($sale->sale_status == 2)
                                                <span class="badge badge-danger">Pending</span>
                                                @elseif($sale->sale_status == 3)
                                                <span class="badge badge-warning">Canceled</span>
                                                @else
                                                <span class="badge badge-primary">On The Way</span>
                                                @endif

                                            </div>
                                        </div>
                                        <hr class="mt-3 mb-3">
                                        <div class="d-flex justify-content-between">

                                            <div>
                                                <h4 class="" data-price="{{ ($sale->grand_total - $sale->coupon_discount) }}">
                                                    @if($general_setting->currency_position == 'prefix')
                                                    {{@$currency->symbol ?? @$currency->code}} {{ ($sale->grand_total - $sale->coupon_discount) }}
                                                    @else
                                                    {{ ($sale->grand_total - $sale->coupon_discount) }} {{$currency->symbol ?? $currency->code}}
                                                    @endif
                                                </h4>
                                            </div>
                                            <div>
                                                <a class="btn btn-sm btn-success" href="{{url('customer/order-details')}}/{{$sale->id}}"><span class="material-symbols-outlined">visibility</span></a> &nbsp;&nbsp;
                                                @if($sale->sale_status == 2)<a class="btn btn-sm btn-danger" href="{{url('customer/order-cancel')}}/{{$sale->id}}"><span class="material-symbols-outlined">delete</span></a>@endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                            <div class="card mb-5">
                                <div class="card-body">
                                    <h3>{{__('db.You have not ordered anything yet!')}}</h3>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!--My account Dashboard ends-->
@endsection

@section('script')

@endsection
