@extends('backend.layout.main')

 @section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.delivery_man_profile')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('delivery-men.index') }}">{{__('db.delivery_men_list')}}</a></li>
                    <li class="breadcrumb-item active">{{ $lims_delivery_man_data->name }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($lims_delivery_man_data->image)
                        <img src="{{ asset('images/delivery_man/' . $lims_delivery_man_data->image) }}" class="img-fluid rounded-circle mb-3" style="width:150px;height:150px;object-fit:cover;" alt="{{ $lims_delivery_man_data->name }}">
                    @else
                        <div class="rounded-circle mb-3 d-inline-flex align-items-center justify-content-center bg-light" style="width:150px;height:150px;">
                            <i class="ti ti-user" style="font-size:60px;"></i>
                        </div>
                    @endif
                    <h4>{{ $lims_delivery_man_data->name }}</h4>
                    <span class="badge badge-{{ $lims_delivery_man_data->is_active ? 'success' : 'danger' }}">
                        {{ $lims_delivery_man_data->is_active ? __('db.Active') : __('db.Inactive') }}
                    </span>
                    <div class="mt-3">
                        <a href="{{ route('delivery-men.edit', $lims_delivery_man_data->id) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-edit"></i> {{__('db.edit')}}</a>
                        <a href="{{ route('delivery-men.performance', $lims_delivery_man_data->id) }}" class="btn btn-sm btn-outline-info" target="_blank"><i class="ti ti-chart-bar"></i> {{__('db.Performance')}}</a>
                        <a href="{{ route('delivery-men.assignedCustomers', $lims_delivery_man_data->id) }}" class="btn btn-sm btn-outline-success">
                            <i class="ti ti-users"></i> {{__('db.Customers')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Contact & Documents')}}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tr><th style="width:35%">{{__('db.Phone')}}</th><td>{{ $lims_delivery_man_data->phone_number }}</td></tr>
                        <tr><th>{{__('db.Email')}}</th><td>{{ $lims_delivery_man_data->email ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Address')}}</th><td>{{ $lims_delivery_man_data->address ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.City')}}</th><td>{{ $lims_delivery_man_data->city ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Country')}}</th><td>{{ $lims_delivery_man_data->country ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.NID Number')}}</th><td>{{ $lims_delivery_man_data->nid_number ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.License Number')}}</th><td>{{ $lims_delivery_man_data->license_number ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Warehouse')}}</th><td>{{ $lims_delivery_man_data->warehouse ? $lims_delivery_man_data->warehouse->name : 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Note')}}</th><td>{{ $lims_delivery_man_data->note ?? 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Vehicle Information')}}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tr><th style="width:35%">{{__('db.Vehicle Type')}}</th><td>{{ $lims_delivery_man_data->vehicle_type ?? 'N/A' }}</td></tr>
                        <tr><th>{{__('db.Vehicle Number')}}</th><td>{{ $lims_delivery_man_data->vehicle_number ?? 'N/A' }}</td></tr>
                    </table>
                    @if($lims_delivery_man_data->vehicles && $lims_delivery_man_data->vehicles->isNotEmpty())
                        <h6>{{__('db.Registered Vehicles')}}</h6>
                        <ul class="list-group">
                            @foreach($lims_delivery_man_data->vehicles as $vehicle)
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>{{ $vehicle->vehicle_type ?? 'N/A' }}</span>
                                    <span class="badge badge-secondary">{{ $vehicle->vehicle_number ?? 'N/A' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.delivery_man_assignments')}}</h5>
                </div>
                <div class="card-body">
                    @if($lims_delivery_man_data->assignments && $lims_delivery_man_data->assignments->isNotEmpty())
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{__('db.Route')}}</th>
                                    <th>{{__('db.Area')}}</th>
                                    <th>{{__('db.status')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lims_delivery_man_data->assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->route ? $assignment->route->name : 'N/A' }}</td>
                                        <td>{{ $assignment->route ? ($assignment->route->area ?? 'N/A') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $assignment->is_active ? 'success' : 'secondary' }}">
                                                {{ $assignment->is_active ? __('db.Active') : __('db.Inactive') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">{{__('db.No assignments found')}}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Performance Summary')}}</h5>
                </div>
                <div class="card-body">
                    @if($lims_delivery_man_data->fieldOrders && $lims_delivery_man_data->fieldOrders->isNotEmpty())
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{__('db.Reference')}}</th>
                                    <th>{{__('db.customer')}}</th>
                                    <th>{{__('db.status')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lims_delivery_man_data->fieldOrders as $order)
                                    <tr>
                                        <td>{{ $order->reference_no ?? 'N/A' }}</td>
                                        <td>{{ $order->customer ? $order->customer->name : 'N/A' }}</td>
                                        <td>{{ ucfirst($order->status ?? 'N/A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">{{__('db.No orders found')}}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
