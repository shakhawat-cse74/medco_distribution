@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Delivery Man Dashboard</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Delivery Man Dashboard</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-shopping-cart bg-success rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Total Orders</h6>
                                    <h3>{{ $totalOrders }}</h3>
                                </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-money bg-warning rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Total Collection</h6>
                                    <h3>{{ $totalCollection }}</h3>
                                </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-clock bg-danger rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Pending Deliveries</h6>
                                    <h3>{{ $totalDeliveries - $completedOrders }}</h3>
                                </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-check bg-primary rounded-circle p-3 text-white me-3"></i>
                        <div>
                            <h6>Completed Deliveries</h6>
                            <h3>{{ $totalDeliveries }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Man Info & Stats -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Delivery Man Profile</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($lims_delivery_man_data->image)
                            <img src="{{ asset('images/delivery_man/' . $lims_delivery_man_data->image) }}" class="rounded-circle" width="100" height="100">
                        @else
                            <i class="ti ti-user bg-secondary rounded-circle p-3 text-white" style="width: 100px; height: 100px;"></i>
                        @endif
                        <h5 class="mt-3">{{ $lims_delivery_man_data->name }}</h5>
                        <p class="text-muted">{{ $lims_delivery_man_data->phone_number }}</p>
                    </div>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Vehicle Type:</span>
                            <span>{{ $lims_delivery_man_data->vehicle_type }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Warehouse:</span>
                            <span>{{ $lims_delivery_man_data->warehouse->name ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Status:</span>
                            <span class="badge bg-success">Active</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Performance Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Statistics</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Total Orders:</span>
                                    <span>{{ $totalOrders }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Completed Orders:</span>
                                    <span>{{ $completedOrders }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Completion Rate:</span>
                                    <span>{{ $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0 }}%</span>
                                </div>
                            </div>

                            <h6>Financial Summary</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Total Collection:</span>
                                    <span>{{ $totalCollection }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Due:</span>
                                    <span>{{ $totalDue }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Commission:</span>
                                    <span>{{ $totalCommission }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6>Activity Summary</h6>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Visits:</span>
                                        <span>{{ $totalVisits }}</span>
                                    </div>
                                <div class="d-flex justify-content-between">
                                    <span>Cash Deposits:</span>
                                    <span>{{ $totalDeposits }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Deliveries:</span>
                                    <span>{{ $totalDeliveries }}</span>
                                </div>
                            </div>

                                <div class="alert alert-info">
                                    <h6>Status</h6>
                                    <p class="mb-0">You have {{ $totalOrders - $completedOrders }} pending orders to complete.</p>
                                    <p class="mb-0">{{ $totalDue > 0 ? 'You have ' . $totalDue . ' due amount to collect.' : 'All payments collected.' }}</p>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100 mb-2">Start New Delivery</button>
                            <button class="btn btn-success w-100 mb-2">Mark Delivery Complete</button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning w-100 mb-2">Collect Cash</button>
                            <button class="btn btn-info w-100 mb-2">Update Location</button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-secondary w-100 mb-2">View Schedule</button>
                            <button class="btn btn-dark w-100 mb-2">Customer Reports</button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-danger w-100 mb-2">Emergency Contact</button>
                            <button class="btn btn-light w-100 mb-2">View Messages</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection