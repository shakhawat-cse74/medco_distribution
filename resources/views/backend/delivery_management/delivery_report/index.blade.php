@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Delivery Dashboard')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.Delivery Management')}}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary period-tab {{ $period == 'today' ? 'active' : '' }}" data-period="today">Today</button>
                        <button type="button" class="btn btn-outline-primary period-tab {{ $period == 'week' ? 'active' : '' }}" data-period="week">Weekly</button>
                        <button type="button" class="btn btn-outline-primary period-tab {{ $period == 'month' ? 'active' : '' }}" data-period="month">Monthly</button>
                        <button type="button" class="btn btn-outline-primary period-tab {{ $period == 'custom' ? 'active' : '' }}" data-period="custom">Custom</button>
                    </div>
                    <div class="row g-2" id="filter-row">
                        <div class="col-md-3">
                            <select class="form-select" id="delivery_man_filter">
                                <option value="">{{__('db.All Delivery Men')}}</option>
                                @foreach($lims_delivery_man_list as $dm)
                                    <option value="{{ $dm->id }}" {{ ($selectedDeliveryManId ?? '') == $dm->id ? 'selected' : '' }}>{{ $dm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-none" id="start_date_col">
                            <input type="text" class="form-control datepicker" id="start_date" value="{{ $startDate }}" placeholder="Start date">
                        </div>
                        <div class="col-md-3 d-none" id="end_date_col">
                            <input type="text" class="form-control datepicker" id="end_date" value="{{ $endDate }}" placeholder="End date">
                        </div>
                        <div class="col-md-3 d-none" id="apply_col">
                            <button type="button" class="btn btn-primary w-100" id="applyFilter">Apply</button>
                        </div>
                    </div>
                    <div id="filter-loading" class="d-none mt-3">
                        <div class="d-flex align-items-center text-muted">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            <span>Loading dashboard data...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="stats-container">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{__('db.Total Orders')}}</h6>
                            <h3 class="mb-0" id="total_orders">{{ $stats['total_orders'] }}</h3>
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
                            <h6 class="mb-1">{{__('db.Total Collection')}}</h6>
                            <h3 class="mb-0" id="total_collection">{{ number_format($stats['total_collection'], 2) }}</h3>
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
                            <h6 class="mb-1">{{__('db.Completed Orders')}}</h6>
                            <h3 class="mb-0" id="completed_orders">{{ $stats['completed_orders'] }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-check bg-primary rounded-circle p-2 text-white"></i>
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
                            <h6 class="mb-1">{{__('db.Pending Orders')}}</h6>
                            <h3 class="mb-0" id="pending_orders">{{ $stats['pending_orders'] }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-alert-triangle bg-danger rounded-circle p-2 text-white"></i>
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
                            <h6 class="mb-1">{{__('db.Cancelled Orders')}}</h6>
                            <h3 class="mb-0" id="cancelled_orders">{{ $stats['cancelled_orders'] }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-x bg-secondary rounded-circle p-2 text-white"></i>
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
                            <h6 class="mb-1">{{__('db.Total Due')}}</h6>
                            <h3 class="mb-0" id="total_due">{{ number_format($stats['total_due'], 2) }}</h3>
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
                            <h6 class="mb-1">{{__('db.Total Delivery Men')}}</h6>
                            <h3 class="mb-0" id="total_delivery_men">{{ $stats['total_delivery_men'] }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-users bg-info rounded-circle p-2 text-white"></i>
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
                            <h6 class="mb-1">{{__('db.Pending Deliveries')}}</h6>
                            <h3 class="mb-0" id="pending_deliveries">{{ $stats['pending_deliveries'] }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="ti ti-clock bg-warning rounded-circle p-2 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Orders & Collection Trend')}}</h5>
                </div>
                <div class="card-body">
                    <canvas id="ordersChart" height="80"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Order Status')}}</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Delivery Man Performance')}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="deliveryManTable">
                            <thead>
                                <tr>
                                    <th>{{__('db.Delivery Man')}}</th>
                                    <th>{{__('db.Total Orders')}}</th>
                                    <th>{{__('db.Completed')}}</th>
                                    <th>{{__('db.Pending')}}</th>
                                    <th>{{__('db.Cancelled')}}</th>
                                    <th>{{__('db.Collection')}}</th>
                                    <th>{{__('db.Due')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deliveryManStats as $dm)
                                <tr>
                                    <td>{{ $dm['delivery_man']->name }}</td>
                                    <td>{{ $dm['total_orders'] }}</td>
                                    <td>{{ $dm['completed_orders'] }}</td>
                                    <td>{{ $dm['pending_orders'] }}</td>
                                    <td>{{ $dm['cancelled_orders'] }}</td>
                                    <td>{{ number_format($dm['total_collection'], 2) }}</td>
                                    <td>{{ number_format($dm['total_due'], 2) }}</td>
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

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    let chartData = @json($chartData);
    let stats = @json($stats);
    let ordersChartInstance = null;
    let statusChartInstance = null;

    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    function initCharts() {
        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        if (ordersChartInstance) ordersChartInstance.destroy();
        ordersChartInstance = new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Orders',
                        data: chartData.orders,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Collection',
                        data: chartData.collection,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'y1',
                    },
                    {
                        label: 'Due',
                        data: chartData.due,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { type: 'linear', position: 'left', title: { display: true, text: 'Orders' } },
                    y1: { type: 'linear', position: 'right', title: { display: true, text: 'Amount' }, grid: { drawOnChartArea: false } }
                }
            }
        });

        const statusCtx = document.getElementById('statusChart').getContext('2d');
        if (statusChartInstance) statusChartInstance.destroy();
        statusChartInstance = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [stats.completed_orders, stats.pending_orders, stats.cancelled_orders],
                    backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    initCharts();

    function loadDashboardData(period, startDate, endDate, deliveryManId) {
        $('#filter-loading').removeClass('d-none');
        $.ajax({
            url: '{{ route('delivery-reports.dashboardData') }}',
            type: 'POST',
            data: {
                period: period,
                start_date: startDate,
                end_date: endDate,
                delivery_man_id: deliveryManId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    stats = response.stats;
                    $('#total_orders').text(stats.total_orders);
                    $('#total_collection').text(parseFloat(stats.total_collection).toFixed(2));
                    $('#completed_orders').text(stats.completed_orders);
                    $('#pending_orders').text(stats.pending_orders);
                    $('#cancelled_orders').text(stats.cancelled_orders);
                    $('#total_due').text(parseFloat(stats.total_due).toFixed(2));
                    $('#pending_deliveries').text(stats.pending_deliveries);
                    $('#total_delivery_men').text(stats.total_delivery_men);

                    chartData = response.chartData;
                    initCharts();

                    if (response.deliveryManStats && response.deliveryManStats.length > 0) {
                        let rows = '';
                        $.each(response.deliveryManStats, function(index, dm) {
                            rows += '<tr>' +
                                '<td>' + dm.delivery_man.name + '</td>' +
                                '<td>' + dm.total_orders + '</td>' +
                                '<td>' + dm.completed_orders + '</td>' +
                                '<td>' + dm.pending_orders + '</td>' +
                                '<td>' + dm.cancelled_orders + '</td>' +
                                '<td>' + parseFloat(dm.total_collection).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(dm.total_due).toFixed(2) + '</td>' +
                                '</tr>';
                        });
                        $('#deliveryManTable tbody').html(rows);
                    }
                }
            },
            error: function() {
                alert('Failed to load dashboard data');
            },
            complete: function() {
                $('#filter-loading').addClass('d-none');
            }
        });
    }

    $('.period-tab').on('click', function() {
        const period = $(this).data('period');
        $('.period-tab').removeClass('active');
        $(this).addClass('active');
        if (period === 'custom') {
            $('#start_date_col, #end_date_col, #apply_col').removeClass('d-none');
            $('#start_date').focus();
            return;
        }
        $('#start_date_col, #end_date_col, #apply_col').addClass('d-none');
        const deliveryManId = $('#delivery_man_filter').val();
        loadDashboardData(period, '', '', deliveryManId);
    });

    $('#applyFilter').on('click', function() {
        const period = $('.period-tab.active').data('period') || 'today';
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const deliveryManId = $('#delivery_man_filter').val();
        
        if (period === 'custom' && (!startDate || !endDate)) {
            alert('Please select both start and end dates');
            return;
        }
        
        loadDashboardData(period, startDate, endDate, deliveryManId);
    });

    $('#delivery_man_filter').on('change', function() {
        const period = $('.period-tab.active').data('period') || 'today';
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const deliveryManId = $(this).val();
        
        if (period === 'custom') {
            loadDashboardData(period, startDate, endDate, deliveryManId);
        } else {
            loadDashboardData(period, '', '', deliveryManId);
        }
    });

    $('#start_date, #end_date').on('change', function() {
        const period = $('.period-tab.active').data('period') || 'today';
        if (period === 'custom') {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            if (startDate && endDate) {
                const deliveryManId = $('#delivery_man_filter').val();
                loadDashboardData(period, startDate, endDate, deliveryManId);
            }
        }
    });
});
</script>
@endpush
