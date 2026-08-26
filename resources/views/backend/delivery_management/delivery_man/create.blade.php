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
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
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
                                <label for="license_number">{{__('db.License Number')}}</label>
                                <input type="text" class="form-control" id="license_number" name="license_number">
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
                                <label for="warehouse_id">{{__('db.Warehouse')}}</label>
                                <select class="form-control" id="warehouse_id" name="warehouse_id">
                                    <option value="">{{__('db.Select Warehouse')}}</option>
                                    @foreach($lims_warehouse_list as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="user_id">{{__('db.User')}}</label>
                                <select class="form-control" id="user_id" name="user_id">
                                    <option value="">{{__('db.Select User')}}</option>
                                    @foreach($lims_user_list as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="image">{{__('db.Photo')}}</label>
                                <input type="file" class="form-control" id="image" name="image">
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="note">{{__('db.Note')}}</label>
                                <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                            </div>
                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                    <label class="custom-control-label" for="is_active">{{__('db.Active')}}</label>
                                </div>
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
