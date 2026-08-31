@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Delivery Charge Settings')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.Delivery Charge Settings')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Delivery Charge Configuration')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('delivery-settings.updateDeliveryChargeSettings') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Charge Type')}}</label>
                                <select class="form-control" name="delivery_charge_type">
                                    <option value="flat" {{ setting('delivery_charge_type', 'delivery') == 'flat' ? 'selected' : '' }}>{{__('db.Flat Rate')}}</option>
                                    <option value="distance" {{ setting('delivery_charge_type', 'delivery') == 'distance' ? 'selected' : '' }}>{{__('db.Distance-based')}}</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Default Delivery Charge')}}</label>
                                <input type="number" step="0.01" class="form-control" name="default_delivery_charge" value="{{ setting('default_delivery_charge', 'delivery') ?? 0 }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Free Delivery Above')}}</label>
                                <input type="number" step="0.01" class="form-control" name="free_delivery_above" value="{{ setting('free_delivery_above', 'delivery') ?? 0 }}">
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
