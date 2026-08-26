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
            <div class="row">
                <div class="col-12">
                    <div class="page-header">
                        <div class="page-block">
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <h3 class="page-title">My Dashboard</h3>
                                    <ul class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('delivery-man.dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                                    </ul>
                                </div>
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
                                    <h3>{{ $totalOrders }}</h3>
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
                                    <h3>{{ $completedOrders }}</h3>
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
                                    <h3>{{ $pendingOrders }}</h3>
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
                                    <h3>{{ $cancelledOrders }}</h3>
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
                                <i class="ti ti-calendar bg-info rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Today Orders</h6>
                                    <h3>{{ $todayOrders }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-money bg-success rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Today Collection</h6>
                                    <h3>{{ number_format($todayCollection, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-report bg-primary rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Week Orders</h6>
                                    <h3>{{ $weekOrders }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-chart-bar bg-warning rounded-circle p-3 text-white me-3"></i>
                                <div>
                                    <h6>Month Orders</h6>
                                    <h3>{{ $monthOrders }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>My Profile</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                @if($deliveryMan->image)
                                    <img src="{{ asset('images/delivery_man/' . $deliveryMan->image) }}" class="rounded-circle" width="100" height="100">
                                @else
                                    <i class="ti ti-user bg-secondary rounded-circle p-3 text-white" style="width: 100px; height: 100px;"></i>
                                @endif
                                <h5 class="mt-3">{{ $deliveryMan->name }}</h5>
                                <p class="text-muted">{{ $deliveryMan->phone_number }}</p>
                            </div>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Email:</span>
                                    <span>{{ $deliveryMan->email }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Vehicle:</span>
                                    <span>{{ $deliveryMan->vehicle_type ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Warehouse:</span>
                                    <span>{{ $deliveryMan->warehouse->name ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Status:</span>
                                    <span class="badge bg-{{ $deliveryMan->is_active ? 'success' : 'danger' }}">{{ $deliveryMan->is_active ? 'Active' : 'Inactive' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Financial Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Total Collection:</span>
                                            <strong>{{ number_format($totalCollection, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Total Due:</span>
                                            <strong class="text-danger">{{ number_format($totalDue, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Week Collection:</span>
                                            <strong>{{ number_format($weekCollection, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Month Collection:</span>
                                            <strong>{{ number_format($monthCollection, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <h6>Status Overview</h6>
                                        <p class="mb-1">Today: {{ $todayOrders }} orders, Collection: {{ number_format($todayCollection, 2) }}, Due: {{ number_format($todayDue, 2) }}</p>
                                        <p class="mb-0">You have {{ $pendingOrders }} pending orders to complete.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Recent Orders</h5>
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
</body>
</html>
