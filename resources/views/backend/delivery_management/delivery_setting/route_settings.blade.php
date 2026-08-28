@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Route Settings')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.Route Settings')}}</li>
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
                    <h5 class="m-b-0">{{__('db.Route Configuration')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('delivery-settings.updateRouteSettings') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Default Route Optimization')}}</label>
                                <select class="form-control" name="default_route_optimization">
                                    <option value="1" {{ setting('default_route_optimization', 'delivery') == 1 ? 'selected' : '' }}>{{__('db.Enabled')}}</option>
                                    <option value="0" {{ setting('default_route_optimization', 'delivery') == 0 ? 'selected' : '' }}>{{__('db.Disabled')}}</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Max Stops per Route')}}</label>
                                <input type="number" class="form-control" name="max_stops_per_route" value="{{ setting('max_stops_per_route', 'delivery') ?? 20 }}">
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
