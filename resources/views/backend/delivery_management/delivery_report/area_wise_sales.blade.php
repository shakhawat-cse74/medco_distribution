@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Area Wise Sales Report</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Area Wise Sales Report</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="card">
        <div class="card-header">
            <h5>Sales Analysis by Area/City</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Area/City</th>
                            <th>Total Orders</th>
                            <th>Total Sales Amount</th>
                            <th>Average Order Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($areaSales as $area)
                        <tr>
                            <td>{{ $area->delivery_city }}</td>
                            <td>{{ $area->total_orders }}</td>
                            <td>{{ $area->total_sales }}</td>
                            <td>{{ $area->total_orders > 0 ? round($area->total_sales / $area->total_orders, 2) : 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection