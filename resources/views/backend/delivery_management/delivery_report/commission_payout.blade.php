@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Commission Payout</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Commission Payout</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="card">
        <div class="card-header">
            <h5>Pending Commission Payouts by Delivery Man</h5>
        </div>
        <div class="card-body">
            @foreach($grouped as $deliveryManId => $commissions)
            <div class="card mb-3">
                <div class="card-header">
                    <h6>{{ $commissions->first()->deliveryMan->name }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Field Order</th>
                                    <th>Commission Type</th>
                                    <th>Commission Value</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commissions as $commission)
                                <tr>
                                    <td>{{ $commission->fieldOrder->reference_no ?? 'N/A' }}</td>
                                    <td>{{ $commission->commission_type }}</td>
                                    <td>{{ $commission->commission_value }}%</td>
                                    <td>{{ $commission->commission_amount }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">Process Payout</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection