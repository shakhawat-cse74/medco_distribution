<!DOCTYPE html>
<html dir="@if (Config::get('app.locale') == 'ar' || (gen_setting()->is_rtl ?? false)) {{ 'rtl' }} @endif">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{ url('logo', gen_setting()->favicon ?? gen_setting()->site_logo) }}" />
    <title>Delivery Dashboard - {{ $deliveryMan->name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap-datepicker.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/style.default.css') }}" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="page">
        <header class="header">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <a class="navbar-brand" href="{{ route('delivery-man.dashboard') }}">
                        <i class="ti ti-motorbike"></i> Delivery Panel
                    </a>
                    <div class="ml-auto d-flex align-items-center">
                        <span class="mr-3">{{ $deliveryMan->name }}</span>
                        <form method="POST" action="{{ route('delivery-man.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Logout</button>
                        </form>
                    </div>
                </div>
            </nav>
        </header>

        <div class="page-block">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-b-0">Quick Access</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="{{ url('/delivery-reports') }}" target="_blank" class="btn btn-outline-primary btn-lg w-100 mb-2">
                                        <i class="ti ti-report me-2"></i> Delivery Reports
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ url('/delivery-man-delivery') }}" target="_blank" class="btn btn-outline-info btn-lg w-100 mb-2">
                                        <i class="ti ti-map me-2"></i> Delivery Management
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary period-tab active" data-period="today">Today</button>
                                <button type="button" class="btn btn-outline-primary period-tab" data-period="week">Weekly</button>
                                <button type="button" class="btn btn-outline-primary period-tab" data-period="month">Monthly</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-shopping-cart bg-success rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Total Orders</h6>
                                    <h3 id="total_orders">{{ $totalOrders }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-check bg-primary rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Completed</h6>
                                    <h3 id="completed_orders">{{ $completedOrders }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-clock bg-warning rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Pending</h6>
                                    <h3 id="pending_orders">{{ $pendingOrders }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-x bg-danger rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Cancelled</h6>
                                    <h3 id="cancelled_orders">{{ $cancelledOrders }}</h3>
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
                                <i class="ti ti-money bg-success rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Total Collection</h6>
                                    <h3 id="total_collection">{{ number_format($totalCollection, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-credit-card bg-danger rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Total Due</h6>
                                    <h3 id="total_due">{{ number_format($totalDue, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-chart-bar bg-info rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Total Deliveries</h6>
                                    <h3>{{ $totalDeliveries }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-cash bg-warning rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Total Commission</h6>
                                    <h3>{{ number_format($totalCommission, 2) }}</h3>
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
                            <h5 class="m-b-0">Orders & Collection Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="ordersChart" height="80"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-b-0">Order Status</h5>
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
                            <h5 class="m-b-0">Recent Orders</h5>
                        </div>
                        <div class="card-body">
                            @if($recentOrders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Ref No</th>
                                                <th>Customer</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentOrders as $order)
                                                <tr>
                                                    <td>{{ $order->reference_no }}</td>
                                                    <td>{{ $order->customer->name ?? 'N/A' }}</td>
                                                    <td>{{ number_format($order->grand_total, 2) }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($order->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No orders found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    $(document).ready(function() {
        const chartData = @json($chartData);
        let ordersChartInstance = null;
        let statusChartInstance = null;

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
                        data: [{{ $completedOrders }}, {{ $pendingOrders }}, {{ $cancelledOrders }}],
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

        $('.period-tab').on('click', function() {
            const period = $(this).data('period');
            $('.period-tab').removeClass('active');
            $(this).addClass('active');
            window.location.href = '{{ route('delivery-man.dashboard') }}?period=' + period;
        });
    });
    </script>
</body>
</html>
