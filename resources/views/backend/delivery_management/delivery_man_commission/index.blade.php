@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Commission Management</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Commission Management</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('delivery-man-commissions.settings') }}" class="btn btn-primary">Commission Settings</a>
                        <a href="{{ route('delivery-man-commissions.slabs') }}" class="btn btn-secondary">Commission Slabs</a>
                        <a href="{{ route('delivery-man-commissions.payout-report') }}" class="btn btn-success">Payout Report</a>
                        <a href="{{ route('delivery-man-commissions.new-customer-incentives') }}" class="btn btn-info">New Customer Incentives</a>
                        <a href="{{ route('delivery-man-commissions.due-collection-incentives') }}" class="btn btn-warning">Due Collection Incentives</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5>Commission Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Total Commissions</h6>
                                <h3 class="text-primary">${{ $lims_commission_list->sum('commission_amount') }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Pending Commissions</h6>
                                <h3 class="text-warning">${{ $lims_commission_list->where('status', 'pending')->sum('commission_amount') }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Paid Commissions</h6>
                                <h3 class="text-success">${{ $lims_commission_list->where('status', 'paid')->sum('commission_amount') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Recent Commissions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Delivery Man</th>
                                    <th>Order</th>
                                    <th>Type</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lims_commission_list as $commission)
                                <tr>
                                    <td>{{ $commission->deliveryMan->name }}</td>
                                    <td>{{ $commission->fieldOrder->reference_no ?? 'N/A' }}</td>
                                    <td>{{ $commission->commission_type }}</td>
                                    <td>{{ $commission->commission_rate }}%</td>
                                    <td>{{ $commission->commission_amount }}</td>
                                    <td>
                                        @if($commission->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @else
                                            <span class="badge bg-success">Paid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">View Details</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection