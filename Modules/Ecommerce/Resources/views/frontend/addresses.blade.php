@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@push('css')
<style>
.default-address{border: 1px solid var(--theme-color)}
.address-card .btn{color:var(--theme-color);font-size:18px}
</style>
@endpush

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">{{__('db.My Addresses')}}</h1>
                    <ul>
                        <li><a href="{{url('customer/profile')}}">{{__('db.dashboard')}}</a></li>
                        <li class="active">{{__('db.My Addresses')}}</li>
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
                    <div class="user-sidebar-menu">
                        @include('ecommerce::frontend.customer-menu')
                    </div>
                </div>
                <div class="col-md-9 tabs style1">
                    <div class="row">
                        <div class="col-md-12 mb-5">
                            <div class="user-dashboard-tab__content tab-content">
                                <div class="tab-pane fade show active" id="addresses" role="tabpanel">
                                    <p>The following addresses will be used on the checkout page.</p>
                                    <form class=" mt-3" action="{{ url('customer/address/create') }}" method="post">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <div class="text-block row">
                                                    <div class="form-group col-md-6">
                                                        <input class="form-control" type="text" name="name" placeholder="{{__('db.name')}}">
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <input class="form-control" type="text" name="phone" placeholder="{{__('db.Phone')}}">
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <input class="form-control" type="text" name="address" placeholder="{{__('db.Address')}} *" required>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <input class="form-control" type="text" name="city" placeholder="{{__('db.City')}} *" required>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <input class="form-control" type="text" name="state" placeholder="{{__('db.State')}}">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <input class="form-control" type="text" name="country" placeholder="{{__('db.Country')}} *" required>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <input class="form-control" type="text" name="zip" placeholder="{{__('db.Zip / Postal code')}} *" required>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <input class="form-control" type="hidden" name="customer_id" value="{{$customer->id}}" required>
                                        <button type="submit" class="button style1">Save</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @foreach($addresses as $index=>$address)
                        <div class="col-md-6">
                            <div class="card address-card mb-4 @if($address->default == 1) default-address @endif">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>Address {{$index+1}}</h5>
                                        <div>
                                            <a data-id="{{$address->id}}" class="btn btn-sm favorite @if($address->default == 1) disabled @endif">
                                                <i class="material-symbols-outlined">favorite</i>
                                            </a>
                                            <a data-id="{{$address->id}}" data-toggle="modal" data-target="#edit_address" class="btn btn-sm edit">
                                                <i class="material-symbols-outlined">edit</i>
                                            </a>
                                            <a data-id="{{$address->id}}" class="btn btn-sm delete">
                                                <i class="material-symbols-outlined">delete</i>
                                            </a>
                                        </div>
                                    </div>
                                    <p>{{$address->address}}</p>
                                    <p>
                                        {{$address->city}}
                                        @if(isset($address->state))
                                        , {{$address->state}}
                                        @endif
                                    </p>
                                    <p>{{$address->country}}, {{$address->zip}}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!--My account Dashboard ends-->
    @if(session()->has('message'))
        <div class="alert alert-{{session('type')}} alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session('message') }}</div> 
    @endif

    <div class="modal fade" id="edit_address" tabindex="-1" role="dialog" aria-labelledby="exampleModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="ti-close"></i></span>
                    </button>
                    <div class="row">
                        <div class="col-12">
                            <h4>{{__('db.Edit Address')}}</h4>
                        </div>
                    </div>
                    <form action="{{ url('customer/address/update') }}" method="post">
                    @csrf
                        <div class="text-block row">
                            <div class="form-group col-md-6">
                                <input class="form-control" type="text" name="name" placeholder="{{__('db.name')}}">
                            </div>
                            <div class="form-group col-md-6">
                                <input class="form-control" type="text" name="phone" placeholder="{{__('db.Phone')}}">
                            </div>
                            <div class="form-group col-md-12">
                                <input class="form-control" type="text" name="address" placeholder="{{__('db.Address')}} *" required>
                            </div>
                            <div class="form-group col-md-6">
                                <input class="form-control" type="text" name="city" placeholder="{{__('db.City')}} *" required>
                            </div>
                            <div class="form-group col-md-6">
                                <input class="form-control" type="text" name="state" placeholder="{{__('db.State')}}">
                            </div>
                            <div class="form-group col-md-6">
                                <input class="form-control" type="text" name="country" placeholder="{{__('db.Country')}} *" required>
                            </div>
                            <div class="form-group col-md-6">
                                <input class="form-control" type="text" name="zip" placeholder="{{__('db.Zip / Postal code')}} *" required>
                            </div>
                            <div class="col-12">
                                <input class="form-control" type="hidden" name="id" value="" required>
                                <button type="submit" class="button style1">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
    "use strict";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '.favorite', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = "{{url('customer/address/default/')}}"+"/"+id;
        $('.address-card').removeClass('default-address');
        var fav = $(this);
        $.ajax({
            type: "get",
            url: url,
            success: function(result) {    
                fav.closest('.address-card').addClass('default-address');            
                var message = '<div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Address is saved as default")}}</div>';
                $('body section').append(message);
                $("div.alert").delay(3000).slideUp(800);
            }
        })
    })

    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = "{{url('customer/address/edit/')}}"+"/"+id;
        $.ajax({
            type: "get",
            url: url,
            success: function(result) { 
                $('#edit_address input[name="name"]').val(result.name);
                $('#edit_address input[name="phone"]').val(result.phone);
                $('#edit_address input[name="email"]').val(result.email);
                $('#edit_address input[name="address"]').val(result.address);
                $('#edit_address input[name="city"]').val(result.city);
                $('#edit_address input[name="state"]').val(result.state);
                $('#edit_address input[name="country"]').val(result.country);
                $('#edit_address input[name="zip"]').val(result.zip);
                $('#edit_address input[name="id"]').val(result.id);
            }
        })
    })

    $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = "{{url('customer/address/delete/')}}"+"/"+id;
        var del = $(this);
        $.ajax({
            type: "get",
            url: url,
            success: function(result) {    
                del.closest('.col-md-6').slideUp(800).remove();            
                var message = '<div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Address deleted")}}</div>';
                $('body section').append(message);
                $("div.alert").delay(3000).slideUp(800);
            }
        })
    })
</script>
@endsection