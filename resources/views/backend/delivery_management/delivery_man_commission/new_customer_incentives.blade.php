@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">New Customer Incentives</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">New Customer Incentives</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="card">
        <div class="card-header">
            <h5>New Customer Incentives by Delivery Man</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Delivery Man</th>
                            <th>New Customers (Current Month)</th>
                            <th>Incentive Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $item)
                        <tr>
                            <td>{{ $item['delivery_man']->name }}</td>
                            <td>{{ $item['new_customers'] }}</td>
                            <td>
                                @php
                                    $incentiveAmount = $item['new_customers'] * 100; // $100 per new customer
                                @endphp
                                ${{ $incentiveAmount }}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary">Process Incentive</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection