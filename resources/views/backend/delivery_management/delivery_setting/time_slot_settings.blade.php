@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Time Slot Settings')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.Time Slot Settings')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Time Slot Configuration')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('delivery-settings.updateTimeSlotSettings') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Enable Time Slots')}}</label>
                                <select class="form-control" name="enable_time_slots">
                                    <option value="1" {{ setting('enable_time_slots', 'delivery') == 1 ? 'selected' : '' }}>{{__('db.Enabled')}}</option>
                                    <option value="0" {{ setting('enable_time_slots', 'delivery') == 0 ? 'selected' : '' }}>{{__('db.Disabled')}}</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Time Slot Interval')}} ({{__('db.hours')}})</label>
                                <input type="number" class="form-control" name="time_slot_interval" value="{{ setting('time_slot_interval', 'delivery') ?? 2 }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.First Delivery Time')}}</label>
                                <input type="time" class="form-control" name="first_delivery_time" value="{{ setting('first_delivery_time', 'delivery') ?? '09:00' }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Last Delivery Time')}}</label>
                                <input type="time" class="form-control" name="last_delivery_time" value="{{ setting('last_delivery_time', 'delivery') ?? '18:00' }}">
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
