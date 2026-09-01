@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Customer Visit Check-in</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Customer Visit</li>
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
                    <h5 class="m-b-0">Customer Visit Check-in</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customer-visits.check-in') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="customer_id">Customer <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="customer_id" name="customer_id" data-live-search="true" required>
                                    <option value="">Select Customer</option>
                                    @foreach($lims_customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="delivery_man_id">Delivery Man <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="delivery_man_id" name="delivery_man_id" data-live-search="true" required>
                                    <option value="">Select Delivery Man</option>
                                    @foreach($lims_delivery_men as $dm)
                                        <option value="{{ $dm->id }}">{{ $dm->name }} - {{ $dm->phone_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Check-in GPS Location</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="check_in_latitude" name="check_in_latitude" placeholder="Latitude">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="check_in_longitude" name="check_in_longitude" placeholder="Longitude">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-check"></i> Check-in
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection