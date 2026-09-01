@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Delivery Settings')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.Delivery Settings')}}</li>
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
                    <h5 class="m-b-0">{{__('db.General Settings')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('delivery-settings.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Auto Assign Orders')}}</label>
                                <select class="form-control" name="auto_assign_orders">
                                    <option value="1" {{ setting('auto_assign_orders', 'delivery') == 1 ? 'selected' : '' }}>{{__('db.Enabled')}}</option>
                                    <option value="0" {{ setting('auto_assign_orders', 'delivery') == 0 ? 'selected' : '' }}>{{__('db.Disabled')}}</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Minimum Order Amount')}}</label>
                                <input type="number" step="0.01" class="form-control" name="minimum_order_amount" value="{{ setting('minimum_order_amount', 'delivery') ?? 0 }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Offline Mode')}}</label>
                                <select class="form-control" name="offline_mode">
                                    <option value="1" {{ setting('offline_mode', 'delivery') == 1 ? 'selected' : '' }}>{{__('db.Enabled')}}</option>
                                    <option value="0" {{ setting('offline_mode', 'delivery') == 0 ? 'selected' : '' }}>{{__('db.Disabled')}}</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Cash Deposit Limit')}}</label>
                                <input type="number" step="0.01" class="form-control" name="cash_deposit_limit" value="{{ setting('cash_deposit_limit', 'delivery') ?? 0 }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Signature Capture Mandatory')}}</label>
                                <select class="form-control" name="signature_mandatory">
                                    <option value="1" {{ setting('signature_mandatory', 'delivery') == 1 ? 'selected' : '' }}>{{__('db.Yes')}}</option>
                                    <option value="0" {{ setting('signature_mandatory', 'delivery') == 0 ? 'selected' : '' }}>{{__('db.No')}}</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Photo Capture Mandatory')}}</label>
                                <select class="form-control" name="photo_mandatory">
                                    <option value="1" {{ setting('photo_mandatory', 'delivery') == 1 ? 'selected' : '' }}>{{__('db.Yes')}}</option>
                                    <option value="0" {{ setting('photo_mandatory', 'delivery') == 0 ? 'selected' : '' }}>{{__('db.No')}}</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.OTP Verification')}}</label>
                                <select class="form-control" name="otp_verification">
                                    <option value="1" {{ setting('otp_verification', 'delivery') == 1 ? 'selected' : '' }}>{{__('db.Enabled')}}</option>
                                    <option value="0" {{ setting('otp_verification', 'delivery') == 0 ? 'selected' : '' }}>{{__('db.Disabled')}}</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Delivery Working Hours')}}</label>
                                <input type="text" class="form-control" name="working_hours" value="{{ setting('working_hours', 'delivery') ?? '09:00-18:00' }}" placeholder="09:00-18:00">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">{{__('db.Update Settings')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
