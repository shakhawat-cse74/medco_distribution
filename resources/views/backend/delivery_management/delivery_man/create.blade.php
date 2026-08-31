@extends('backend.layout.main')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.add_delivery_man')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('delivery-men.index') }}">{{__('db.delivery_men_list')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.add_delivery_man')}}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.add_delivery_man')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('delivery-men.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">{{__('db.name')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="phone_number">{{__('db.Phone')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="email">{{__('db.Email')}}</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="password">{{__('db.Password')}}</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="address">{{__('db.Address')}}</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="city">{{__('db.City')}}</label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="country">{{__('db.Country')}}</label>
                                <input type="text" class="form-control" id="country" name="country">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="nid_number">{{__('db.NID Number')}}</label>
                                <input type="text" class="form-control" id="nid_number" name="nid_number">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="routes">{{__('db.Routes')}}</label>
                                <select class="form-control" id="routes" name="route_ids[]" multiple size="5">
                                    @if(isset($lims_route_list) && $lims_route_list->count() > 0)
                                        @foreach($lims_route_list as $route)
                                            <option value="{{ $route->id }}">{{ $route->name }} - {{ $route->city }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>{{__('db.No routes available')}}</option>
                                    @endif
                                </select>
                                <small class="form-text text-muted">{{__('db.Hold Ctrl/Cmd to select multiple routes')}}</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="vehicle_type">{{__('db.Vehicle Type')}}</label>
                                <select class="form-control" id="vehicle_type" name="vehicle_type">
                                    <option value="">{{__('db.Select Vehicle Type')}}</option>
                                    <option value="Motorcycle">Motorcycle</option>
                                    <option value="Car">Car</option>
                                    <option value="Van">Van</option>
                                    <option value="Truck">Truck</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="vehicle_number">{{__('db.Vehicle Number')}}</label>
                                <input type="text" class="form-control" id="vehicle_number" name="vehicle_number">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="brand">{{__('db.Brand')}}</label>
                                <input type="text" class="form-control" id="brand" name="brand">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="model">{{__('db.Model')}}</label>
                                <input type="text" class="form-control" id="model" name="model">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="color">{{__('db.Color')}}</label>
                                <input type="text" class="form-control" id="color" name="color">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="registration_number">{{__('db.Registration Number')}}</label>
                                <input type="text" class="form-control" id="registration_number" name="registration_number">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="license_number">{{__('db.License Number')}}</label>
                                <input type="text" class="form-control" id="license_number" name="license_number">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="registration_expiry">{{__('db.Registration Expiry')}}</label>
                                <input type="date" class="form-control" id="registration_expiry" name="registration_expiry">
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="image">{{__('db.Photo')}}</label>
                                <input type="file" class="form-control" id="image" name="image">
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="vehicle_image">{{__('db.Vehicle Image')}}</label>
                                <input type="file" class="form-control" id="vehicle_image" name="vehicle_image">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> {{__('db.save')}}</button>
                        <a href="{{ route('delivery-men.index') }}" class="btn btn-secondary">{{__('db.cancel')}}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
