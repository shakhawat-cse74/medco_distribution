@extends('backend.layout.main')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.edit')}} {{__('db.delivery_man')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('delivery-men.index') }}">{{__('db.delivery_men_list')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.edit')}} {{ $lims_delivery_man_data->name }}</li>
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
                    <h5 class="m-b-0">{{__('db.edit')}} {{__('db.delivery_man')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('delivery-men.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="id" value="{{ $lims_delivery_man_data->id }}">

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">{{__('db.name')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $lims_delivery_man_data->name) }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="phone_number">{{__('db.Phone')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $lims_delivery_man_data->user->phone ?? '') }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="email">{{__('db.Email')}}</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $lims_delivery_man_data->user->email ?? '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="password">{{__('db.Password')}}</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="text-muted">{{__('db.Leave blank to keep current')}}</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="address">{{__('db.Address')}}</label>
                                <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $lims_delivery_man_data->address) }}</textarea>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="city">{{__('db.City')}}</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $lims_delivery_man_data->city) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="country">{{__('db.Country')}}</label>
                                <input type="text" class="form-control" id="country" name="country" value="{{ old('country', $lims_delivery_man_data->country) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="nid_number">{{__('db.NID Number')}}</label>
                                <input type="text" class="form-control" id="nid_number" name="nid_number" value="{{ old('nid_number', $lims_delivery_man_data->nid_number) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="routes">{{__('db.Routes')}}</label>
                                <select class="form-control" id="routes" name="route_ids[]" multiple size="5">
                                    @if(isset($lims_route_list) && $lims_route_list->count() > 0)
                                        @foreach($lims_route_list as $route)
                                            <option value="{{ $route->id }}" {{ in_array($route->id, $selected_routes) ? 'selected' : '' }}>{{ $route->name }} - {{ $route->city }}</option>
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
                                    <option value="Motorcycle" {{ old('vehicle_type', $lims_vehicle_data->vehicle_type ?? '') == 'Motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                    <option value="Car" {{ old('vehicle_type', $lims_vehicle_data->vehicle_type ?? '') == 'Car' ? 'selected' : '' }}>Car</option>
                                    <option value="Van" {{ old('vehicle_type', $lims_vehicle_data->vehicle_type ?? '') == 'Van' ? 'selected' : '' }}>Van</option>
                                    <option value="Truck" {{ old('vehicle_type', $lims_vehicle_data->vehicle_type ?? '') == 'Truck' ? 'selected' : '' }}>Truck</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="vehicle_number">{{__('db.Vehicle Number')}}</label>
                                <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" value="{{ old('vehicle_number', $lims_vehicle_data->vehicle_number ?? '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="brand">{{__('db.Brand')}}</label>
                                <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand', $lims_vehicle_data->brand ?? '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="model">{{__('db.Model')}}</label>
                                <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $lims_vehicle_data->model ?? '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="color">{{__('db.Color')}}</label>
                                <input type="text" class="form-control" id="color" name="color" value="{{ old('color', $lims_vehicle_data->color ?? '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="registration_number">{{__('db.Registration Number')}}</label>
                                <input type="text" class="form-control" id="registration_number" name="registration_number" value="{{ old('registration_number', $lims_vehicle_data->registration_number ?? '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="license_number">{{__('db.License Number')}}</label>
                                <input type="text" class="form-control" id="license_number" name="license_number" value="{{ old('license_number', $lims_vehicle_data->license_number ?? '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="registration_expiry">{{__('db.Registration Expiry')}}</label>
                                <input type="date" class="form-control" id="registration_expiry" name="registration_expiry" value="{{ old('registration_expiry', $lims_vehicle_data->registration_expiry ?? '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="image">{{__('db.Photo')}}</label>
                                @if(!empty($lims_delivery_man_data->image))
                                <div class="mb-2">
                                    <img src="{{ asset('images/delivery_man/' . $lims_delivery_man_data->image) }}" alt="{{ $lims_delivery_man_data->name }}" class="img-thumbnail" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                    <p class="mt-1"><small class="text-muted">{{__('db.Current Photo')}}</small></p>
                                </div>
                                @endif
                                <input type="file" class="form-control" id="image" name="image">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="vehicle_image">{{__('db.Vehicle Image')}}</label>
                                @if($lims_vehicle_data && $lims_vehicle_data->image)
                                <div class="mb-2">
                                    <img src="{{ asset('images/delivery_man_vehicle/' . $lims_vehicle_data->image) }}" alt="Vehicle" class="img-thumbnail" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                    <p class="mt-1"><small class="text-muted">{{__('db.Current Vehicle Image')}}</small></p>
                                </div>
                                @endif
                                <input type="file" class="form-control" id="vehicle_image" name="vehicle_image">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> {{__('db.update')}}</button>
                        <a href="{{ route('delivery-men.index') }}" class="btn btn-secondary">{{__('db.cancel')}}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
