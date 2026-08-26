@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Cash Reconciliation</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Cash Reconciliation</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="card">
        <div class="card-header">
            <h5>Cash Reconciliation Report</h5>
            <div class="row mt-3">
                <div class="col-md-4">
                    <label>Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $start_date }}">
                </div>
                <div class="col-md-4">
                    <label>End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $end_date }}">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary mt-4" onclick="filterReconciliation()">Filter</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Payments Received</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Delivery Man</th>
                                    <th>Order</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $payment->fieldOrder->deliveryMan->name }}</td>
                                    <td>{{ $payment->fieldOrder->reference_no }}</td>
                                    <td>{{ $payment->amount }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <h4>Total Payments: {{ $payments->sum('amount') }}</h4>
                </div>
                <div class="col-md-6">
                    <h5>Cash Deposits</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Delivery Man</th>
                                    <th>Amount</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deposits as $deposit)
                                <tr>
                                    <td>{{ $deposit->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $deposit->deliveryMan->name }}</td>
                                    <td>{{ $deposit->amount }}</td>
                                    <td>{{ $deposit->note }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <h4>Total Deposits: {{ $deposits->sum('amount') }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection