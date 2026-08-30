@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Commission Report</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Commission Report</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="card">
        <div class="card-header">
            <h5>Commission Tracking Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Delivery Man</th>
                            <th>Field Order</th>
                            <th>Commission Type</th>
                            <th>Commission Value</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lims_commission_list as $commission)
                        <tr>
                            <td>{{ $commission->deliveryMan->name }}</td>
                            <td>{{ $commission->fieldOrder->reference_no ?? 'N/A' }}</td>
                            <td>{{ $commission->commission_type }}</td>
                            <td>{{ $commission->commission_value }}%</td>
                            <td>{{ $commission->status }}</td>
                            <td>{{ $commission->commission_amount }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection