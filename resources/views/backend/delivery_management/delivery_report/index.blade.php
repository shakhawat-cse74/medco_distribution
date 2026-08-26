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
    <form method="GET" action="{{ route('delivery-report.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <select class="form-control" name="period" onchange="this.form.submit()">
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div class="col-md-3" id="custom-date-range" style="display: {{ $period == 'custom' ? 'block' : 'none' }};">
                <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-3" id="custom-date-range-end" style="display: {{ $period == 'custom' ? 'block' : 'none' }};">
                <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Total Delivery Men</h6>
                            <h3 class="mb-0">{{ $stats['total_delivery_men'] }}</h3>
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
                            <h6 class="mb-1">Total Orders</h6>
                            <h3 class="mb-0">{{ $stats['total_orders'] }}</h3>
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
                            <h6 class="mb-1">Total Collection</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_collection'], 2) }}</h3>
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
                            <h3 class="mb-0">{{ $stats['pending_deliveries'] }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-clock bg-danger rounded-circle p-2 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Completed Orders</h6>
                            <h3 class="mb-0">{{ $stats['completed_orders'] }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-check bg-success rounded-circle p-2 text-white"></i>
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
                            <h6 class="mb-1">Pending Orders</h6>
                            <h3 class="mb-0">{{ $stats['pending_orders'] }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-alert-triangle bg-warning rounded-circle p-2 text-white"></i>
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
                            <h6 class="mb-1">Total Due</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_due'], 2) }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-credit-card bg-danger rounded-circle p-2 text-white"></i>
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
                            <h6 class="mb-1">Cancelled Orders</h6>
                            <h3 class="mb-0">{{ $stats['total_orders'] - $stats['completed_orders'] - $stats['pending_orders'] }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-x bg-secondary rounded-circle p-2 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('select[name="period"]').addEventListener('change', function() {
            const customRanges = document.querySelectorAll('#custom-date-range, #custom-date-range-end');
            customRanges.forEach(function(el) {
                el.style.display = this.value === 'custom' ? 'block' : 'none';
            }.bind(this));
        });
    </script>
</div>
@endsection
