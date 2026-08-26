@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Delivery Reports & Analytics</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Delivery Reports</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <!-- Quick Stats Cards -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Total Delivery Men</h6>
                            <h3 class="mb-0">{{ $lims_delivery_man_list->count() }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-users bg-primary rounded-circle p-2 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Total Orders Today</h6>
                            <h3 class="mb-0">{{ $lims_delivery_man_list->sum(function($dm) { return $dm->fieldOrders()->whereDate('created_at', today())->count(); }) }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-shopping-cart bg-success rounded-circle p-2 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Total Collection Today</h6>
                            <h3 class="mb-0">{{ $lims_delivery_man_list->sum(function($dm) { return $dm->fieldOrders()->whereDate('created_at', today())->sum('paid_amount'); }) }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-money bg-warning rounded-circle p-2 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Pending Deliveries</h6>
                            <h3 class="mb-0">{{ $lims_delivery_man_list->sum(function($dm) { return $dm->deliveries()->where('status', 'assigned')->count(); }) }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-clock bg-danger rounded-circle p-2 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Cards Grid -->
    <div class="row mt-4">
        <!-- Individual Delivery Man Dashboard -->
        <div class="col-md-4">
            <a href="{{ route('delivery-men.index') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-user-check bg-primary rounded-circle p-3 text-white mb-3"></i>
                        <h5>Individual Delivery Man Dashboard</h5>
                        <p class="text-muted small">View individual delivery performance and analytics</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Delivery Man Wise Order Report -->
        <div class="col-md-4">
            <a href="{{ route('delivery-report.delivery-man-wise-order') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-report bg-success rounded-circle p-3 text-white mb-3"></i>
                        <h5>Delivery Man Wise Order Report</h5>
                        <p class="text-muted small">Analyze order distribution by delivery man</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Delivery Man Wise Collection Report -->
        <div class="col-md-4">
            <a href="{{ route('delivery-report.delivery-man-wise-collection') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-cash bg-warning rounded-circle p-3 text-white mb-3"></i>
                        <h5>Delivery Man Wise Collection Report</h5>
                        <p class="text-muted small">Track collections and dues by delivery man</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Delivery Performance Report -->
        <div class="col-md-4">
            <a href="{{ route('delivery-report.delivery-performance') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-chart-bar bg-info rounded-circle p-3 text-white mb-3"></i>
                        <h5>Delivery Performance Report</h5>
                        <p class="text-muted small">Analyze delivery efficiency and completion rates</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Area Wise Sales Report -->
        <div class="col-md-4">
            <a href="{{ route('delivery-report.area-wise-sales') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-map bg-danger rounded-circle p-3 text-white mb-3"></i>
                        <h5>Area Wise Sales Report</h5>
                        <p class="text-muted small">Analyze sales by geographical areas</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Commission Report -->
        <div class="col-md-4">
            <a href="{{ route('delivery-report.commission-report') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-coin bg-primary rounded-circle p-3 text-white mb-3"></i>
                        <h5>Commission Report</h5>
                        <p class="text-muted small">Track delivery man commissions</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cash Reconciliation -->
        <div class="col-md-4">
            <a href="{{ route('delivery-report.cash-reconciliation') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-balance bg-success rounded-circle p-3 text-white mb-3"></i>
                        <h5>Cash Reconciliation</h5>
                        <p class="text-muted small">Reconcile cash collections and deposits</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Customer Visit Report -->
        <div class="col-md-4">
            <a href="{{ route('delivery-report.customer-visit-report') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-map-pin bg-warning rounded-circle p-3 text-white mb-3"></i>
                        <h5>Customer Visit Report</h5>
                        <p class="text-muted small">Track customer visit patterns</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Product Wise Field Sale -->
        <div class="col-md-4">
            <a href="{{ route('delivery-report.product-wise-field-sale') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-package bg-info rounded-circle p-3 text-white mb-3"></i>
                        <h5>Product Wise Field Sale</h5>
                        <p class="text-muted small">Analyze product sales in field operations</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Commission Payout -->
        <div class="col-md-4">
            <a href="{{ route('delivery-report.commission-payout') }}" class="text-decoration-none">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-credit-card bg-danger rounded-circle p-3 text-white mb-3"></i>
                        <h5>Commission Payout</h5>
                        <p class="text-muted small">Process pending commission payouts</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection