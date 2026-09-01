@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Add New Route')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('delivery-man.dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('delivery-man-routes.index') }}">{{__('db.Routes')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.Add New')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Route Information')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('delivery-man-routes.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('db.Route Name')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('db.City')}}</label>
                                    <input type="text" class="form-control" name="city">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('db.Zone')}}</label>
                                    <input type="text" class="form-control" name="zone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('db.Delivery Charge')}} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="delivery_charge" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('db.Estimated Days')}} <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="estimated_days" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('db.Status')}}</label>
                                    <select class="form-control" name="is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{__('db.Note')}}</label>
                            <textarea class="form-control" name="note" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{__('db.Save')}}</button>
                        <a href="{{ route('delivery-man-routes.index') }}" class="btn btn-secondary">{{__('db.Cancel')}}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
