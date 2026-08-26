@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Delivery Man Wise Collection Report</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Delivery Man Wise Collection Report</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="card">
        <div class="card-header">
            <h5>Collection and Due Summary by Delivery Man</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Delivery Man</th>
                            <th>Total Collection</th>
                            <th>Total Due</th>
                            <th>Collection Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $item)
                        <tr>
                            <td>{{ $item['delivery_man']->name }}</td>
                            <td>{{ $item['total_collection'] }}</td>
                            <td>{{ $item['total_due'] }}</td>
                            <td>
                                @php
                                    $total = $item['total_collection'] + $item['total_due'];
                                    $rate = $total > 0 ? round(($item['total_collection'] / $total) * 100, 2) : 0;
                                @endphp
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: {{ $rate }}%">{{ $rate }}%</div>
                                </div>
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