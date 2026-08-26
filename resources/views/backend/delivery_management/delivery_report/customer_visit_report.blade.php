@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Customer Visit Report</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Customer Visit Report</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="card">
        <div class="card-header">
            <h5>Monthly Customer Visit Analysis</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Phone Number</th>
                            <th>Delivery Man</th>
                            <th>Visit Date</th>
                            <th>Next Visit Date</th>
                            <th>Visit Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lims_visit_list as $visit)
                        <tr>
                            <td>{{ $visit->customer->name }}</td>
                            <td>{{ $visit->customer->phone_number }}</td>
                            <td>{{ $visit->deliveryMan->name }}</td>
                            <td>{{ $visit->check_in_at->format('Y-m-d') }}</td>
                            <td>{{ $visit->next_visit_date ? $visit->next_visit_date->format('Y-m-d') : 'N/A' }}</td>
                            <td>{{ $visit->note }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection