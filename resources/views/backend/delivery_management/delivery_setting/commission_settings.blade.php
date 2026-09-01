@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Commission Settings')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.Commission Settings')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Commission Configuration')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('delivery-settings.updateCommissionSettings') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Commission Type')}}</label>
                                <select class="form-control" name="commission_type">
                                    <option value="percentage" {{ setting('commission_type', 'delivery') == 'percentage' ? 'selected' : '' }}>{{__('db.Percentage of Sale')}}</option>
                                    <option value="flat" {{ setting('commission_type', 'delivery') == 'flat' ? 'selected' : '' }}>{{__('db.Flat per Order')}}</option>
                                    <option value="target" {{ setting('commission_type', 'delivery') == 'target' ? 'selected' : '' }}>{{__('db.Target-based Bonus')}}</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Default Commission Rate')}} (%)</label>
                                <input type="number" step="0.01" class="form-control" name="default_commission_rate" value="{{ setting('default_commission_rate', 'delivery') ?? 5 }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Minimum Orders for Bonus')}}</label>
                                <input type="number" class="form-control" name="minimum_orders_for_bonus" value="{{ setting('minimum_orders_for_bonus', 'delivery') ?? 50 }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Bonus Rate')}} (%)</label>
                                <input type="number" step="0.01" class="form-control" name="bonus_rate" value="{{ setting('bonus_rate', 'delivery') ?? 7 }}">
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
