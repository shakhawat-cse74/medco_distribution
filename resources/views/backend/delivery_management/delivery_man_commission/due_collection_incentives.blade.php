@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Due Collection Incentives</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Due Collection Incentives</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="card">
        <div class="card-header">
            <h5>Due Collection Incentives by Delivery Man</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Delivery Man</th>
                            <th>Due Amount Collected</th>
                            <th>Incentive Rate</th>
                            <th>Incentive Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $item)
                        <tr>
                            <td>{{ $item['delivery_man']->name }}</td>
                            <td>{{ $item['due_collected'] }}</td>
                            <td>5%</td>
                            <td>
                                @php
                                    $incentiveAmount = $item['due_collected'] * 0.05;
                                @endphp
                                ${{ number_format($incentiveAmount, 2) }}
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