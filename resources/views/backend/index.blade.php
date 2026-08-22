@php
    $asset_prefix = !config('database.connections.saleprosaas_landlord') ? '' : '../../';
@endphp
@extends('backend.layout.main')
@section('content')

@push('css')
<style>
.bootstrap-select:not([class*="col-"]):not([class*="form-control"]):not(.input-group-btn) {width: auto;}
.legend{width: 10px;height: 10px;border-radius: 50%;margin: 0 5px;display: inline-block;}
.legend-label{font-size: 0.8em!important;color: #555;}
</style>
@endpush

    <x-success-message key="message" />
    <x-error-message key="not_permitted" />

    @php
    $theme = gen_setting()?->theme ?? 'default.css';

    $color = '#733686';
    $color_rgba = 'rgba(115, 54, 134, 0.8)';

    if ($theme === 'default.css') {
        $color = '#733686';
        $color_rgba = 'rgba(115, 54, 134, 0.8)';
    } elseif ($theme === 'green.css') {
        $color = '#2ecc71';
        $color_rgba = 'rgba(46, 204, 113, 0.8)';
    } elseif ($theme === 'blue.css') {
        $color = '#3498db';
        $color_rgba = 'rgba(52, 152, 219, 0.8)';
    } elseif ($theme === 'dark.css') {
        $color = '#34495e';
        $color_rgba = 'rgba(52, 73, 94, 0.8)';
    }
@endphp
    <div class="row">

        <div class="container-fluid">
            @php
                $lims_warehouse_list = App\Models\Warehouse::where('is_active', true)->get();
            @endphp

            @if (!config('database.connections.saleprosaas_landlord') && \Auth::user()->role_id <= 2)
                @if (isset($versionUpgradeData['alert_version_upgrade_enable']) &&
                        $versionUpgradeData['alert_version_upgrade_enable'] == true)
                    <div class="col-12">
                        <div id="alertSection" class="alert not-slide alert-primary alert-dismissible fade show" role="alert">
                            <p id="announce"><strong>Announce !!!</strong> A new version
                                {{ $versionUpgradeData['demo_version'] }} has been released. Please <i><b><a
                                            href="{{ route('new-release') }}">Click here</a></b></i> to check upgrade details.
                            </p>
                            <button type="button" id="closeButtonUpgrade" class="close" data-dismiss="alert"
                                aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                @endif
            @endif
            <div class="col-12">
                <div class="brand-text float-left mt-4">
                    <h3 style="font-size:1em">{{ __('db.welcome') }} <span>{{ Auth::user()->name }}</span></h3>
                </div>
                @if (in_array('restaurant', explode(',', cache()->get('general_setting')->modules)))
                    @if (Auth::user()->role_id > 2 && isset(Auth::user()->service_staff))
                        @php
                            $cooked = DB::table('sales')
                                ->where('waiter_id', Auth::user()->id)
                                ->where('sale_status', 5)
                                ->orWhere('sale_status', 6)
                                ->where('sales.created_at', '>=', now()->subDay())
                                ->count();
                        @endphp
                    @elseif(Auth::user()->role_id <= 2)
                        @php
                            $cooked = DB::table('sales')
                                ->where('sale_status', 6)
                                ->where('sales.created_at', '>=', now()->subDay())
                                ->count();
                        @endphp
                    @endif
                @endif
                @if (in_array('restaurant', explode(',', cache()->get('general_setting')->modules)) && isset($cooked) && $cooked > 0)
                    <a href="{{ route('restaurant.kitchen.dashboard') }}">
                        <div class="alert alert-warning alert-dismissible text-center mb-2">
                            <strong>{{ $cooked }} {{ __('db.Orders to serve') }}</strong>
                        </div>
                    </a>
                @endif

                @php
                    $revenue_profit_summary = $role_has_permissions_list
                        ->where('name', 'revenue_profit_summary')
                        ->first();
                @endphp
                @if ($revenue_profit_summary)
                    <div class="filter-toggle btn-group d-inline-block">
                        <div class="dashboard-filters">
                            @if (\Auth::user()->role_id <= 2)
                            {{-- Warehouse --}}

                            <div class="filter-toggle btn-group mt-0" style=" border: 1px solid #7c5cc4; border-radius: 5px;">
                                <select name="warehouse_id" class="selectpicker" id="warehouse_btn"
                                    data-live-search="true" data-live-search-style="begins">
                                    <option value="0" data-content="<i class='ti ti-map-pin mr-1'></i> {{ __('db.All Warehouse') }}">{{ __('db.All Warehouse') }}</option>

                                    @foreach ($lims_warehouse_list as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            {{-- Date Range --}}
                            <div id="dashboard-datepicker" class="ml-2">
                                <div class="input-group input-group-md">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="ti ti-calendar text-primary"></i>
                                        </span>
                                    </div>

                                    <input type="text"
                                        class="daterangepicker-field form-control border-left-0"
                                        placeholder="Select Date" style="padding-left:0;min-width: 200px;height:40px"/>

                                    <input type="hidden" name="start_date" value="" />
                                    <input type="hidden" name="end_date" value="" />
                                </div>
                            </div>

                        </div>

                    </div>

                @endif
            </div>
        </div>
    </div>
    <!-- Counts Section -->
    <section class="dashboard-counts pt-0">
        <div class="container-fluid">
            <div class="row">
                @if ($revenue_profit_summary)
                    <style>
                        .dashboard-filters {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                        }
                        /* Modern Dashboard Widget Styles */
                        .dashboard-widget {
                            background: #fff;
                            border-radius: 15px; /* Large rounded corners */
                            padding: 20px;
                            margin-bottom: 24px;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); /* Soft shadow */
                            display: flex;
                            align-items: center;
                            transition: transform 0.2s;
                            border: 1px solid #f0f0f0;
                            text-decoration: none !important;
                        }

                        @media (max-width: 767px) {
                            .dashboard-filters {
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                gap: 10px;
                            }
                            .dashboard-widget {
                                flex-direction: column;
                                text-align: center;
                                margin-bottom: 15px
                            }
                            .widget-icon-container {
                                margin-right: 0 !important;
                                margin-bottom: 10px;
                            }
                        }

                        .dashboard-widget:hover {
                            transform: translateY(-5px);
                        }

                        .widget-icon-container {
                            width: 56px;
                            height: 56px;
                            border-radius: 12px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 24px;
                            margin-right: 15px;
                        }

                        /* Light backgrounds for icons */
                        .bg-light-purple { background-color: #f3e8ff; color: #733686; }
                        .bg-light-cyan { background-color: #e0f7fa; color: #0584a0; }
                        .bg-light-orange { background-color: #fff3e0; color: #ff8952; }
                        .bg-light-red { background-color: #ffebee; color: #f66162; }
                        .bg-light-gold { background-color: #fef9c3; color: #d48519; }
                        .bg-light-yellow { background-color: #fefce8; color: #bdbb39; }
                        .bg-light-green { background-color: #ecfdf5; color: #00c689; }
                        .bg-light-blue { background-color: #eff6ff; color: #297ff9; }

                        .widget-content {
                            flex-grow: 1;
                        }

                        .widget-label {
                            font-size: 0.85rem;
                            color: #64748b;
                            margin-bottom: 4px;
                            font-weight: 500;
                            display: block;
                        }

                        .widget-value {
                            font-size: 1rem !important;
                            font-weight: 600;
                            color: #1e293b;
                            display: block;
                        }
                        .dark-mode .dashboard-widget {
                            background: #283046;
                            border: 1px solid #283046;
                        }
                        .dark-mode .widget-label {
                            color: #d0d2d6;
                        }

                        .dark-mode .widget-value {
                            color: #f0f0f0;
                        }
                        .spin-loading {
                            font-size: 16px !important;
                            display: inline-block; /* Required for rotation to work correctly */
                            animation: spin 1s linear infinite;
                        }

                        @keyframes spin {
                            from {
                                transform: rotate(0deg);
                            }
                            to {
                                transform: rotate(360deg);
                            }
                        }
                    </style>
                    <div class="col-md-12 mb-3 mt-3">
                        <div class="d-flex flex-wrap gap-2" style="gap: 10px;">
                            <button type="button" data-toggle="modal" data-target="#quickInvoiceModal" class="btn btn-primary shadow-sm">
                                <i class="ti ti-plus mr-1"></i> {{ __('Quick Invoice') ?? 'Create Invoice' }}
                            </button>
                            <a href="{{ route('products.create') }}" class="btn btn-info shadow-sm text-white">
                                <i class="ti ti-box mr-1"></i> {{ __('Quick Add Product') ?? 'Quick Add Product' }}
                            </a>
                            <a href="{{ route('sales.index') }}" class="btn btn-success shadow-sm">
                                <i class="ti ti-cash mr-1"></i> {{ __('Receive Payment') ?? 'Receive Payment' }}
                            </a>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-6 col-lg-3">
                                <a href="{{route('sales.index')}}" class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-purple">
                                        <i class="ti ti-chart-bar"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('db.Sale') }}</span>
                                        <span class="widget-value total_sale-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-cyan">
                                        <i class="ti ti-invoice"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('Total Customer Due') ?? 'Total Customer Due' }}</span>
                                        <span class="widget-value invoice-due-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <a href="{{route('return-sale.index')}}" class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-orange">
                                        <i class="ti ti-arrow-back"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('db.Sale Return') }}</span>
                                        <span class="widget-value return-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-red">
                                        <i class="ti ti-wallet"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('db.Expense') }}</span>
                                        <span class="widget-value expense-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <a href="{{route('purchases.index')}}" class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-gold">
                                        <i class="ti ti-download"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('db.Purchase') }}</span>
                                        <span class="widget-value total_purchase-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- Count item widget-->
                            <div class="col-6 col-lg-3">
                                <div class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-yellow">
                                        <i class="ti ti-credit-card-refund"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('Total Supplier Due') ?? 'Total Supplier Due' }}</span>
                                        <span class="widget-value purchase_due-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Count item widget-->
                            <div class="col-6 col-lg-3">
                                <a href="{{route('return-purchase.index')}}"  class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-green">
                                        <i class="ti ti-arrow-forward-up"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('db.Purchase Return') }}</span>
                                        <span class="widget-value purchase_return-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-blue">
                                        <i class="ti ti-trophy"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('db.profit') }}</span>
                                        <span class="widget-value profit-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-green">
                                        <i class="ti ti-money"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('Stock Value') ?? 'Stock Value' }}</span>
                                        <span class="widget-value stock-value-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <a href="{{route('report.qtyAlert')}}" class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-red">
                                        <i class="ti ti-alert-triangle"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('Stock Alerts') ?? 'Stock Alerts' }}</span>
                                        <span class="widget-value stock-alert-data">
                                            0 <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-cyan">
                                        <i class="ti ti-receipt"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('Payment Daily') ?? 'Payment (Daily)' }}</span>
                                        <span class="widget-value payment-daily-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="dashboard-widget">
                                    <div class="widget-icon-container bg-light-purple">
                                        <i class="ti ti-calendar-event"></i>
                                    </div>
                                    <div class="widget-content">
                                        <span class="widget-label">{{ __('Payment Monthly') ?? 'Payment (Monthly)' }}</span>
                                        <span class="widget-value payment-monthly-data">
                                            {{ number_format((float) 0.00, gen_setting()->decimal, '.', '') }} <i class="ti ti-reload spin-loading" style="font-size: 2rem; color: #3b82f6;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @php
                    $cash_flow = $role_has_permissions_list->where('name', 'cash_flow')->first();
                @endphp
                @if ($cash_flow)
                    <div class="col-md-8 mt-2">
                        <div class="card line-chart">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>{{ __('db.Cash Flow') }}</h4>
                                <div class="legends ml-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="legend" style="background-color: #733686;"></span>
                                        <span class="legend-label">{{ __('db.Payment Recieved') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="legend" style="background-color: #6fb1b5;"></span>
                                        <span class="legend-label">{{ __('db.Payment Sent') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-1">
                                <canvas id="cashFlow" data-color = "{{ $color }}"
                                    data-color_rgba = "{{ $color_rgba }}"
                                    data-recieved = "{{ json_encode($payment_recieved) }}"
                                    data-sent = "{{ json_encode($payment_sent) }}"
                                    data-month = "{{ json_encode($month) }}"
                                    data-label1="{{ __('db.Payment Recieved') }}"
                                    data-label2="{{ __('db.Payment Sent') }}"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
                @php
                    $monthly_summary = $role_has_permissions_list->where('name', 'monthly_summary')->first();
                @endphp
                @if ($monthly_summary)
                    <div class="col-md-4 mt-2">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>{{ date('F') }} {{ date('Y') }}</h4>
                            </div>
                            <div class="pie-chart mb-2">
                                <canvas id="transactionChart" data-color = "{{ $color }}"
                                    data-color_rgba = "{{ $color_rgba }}" data-revenue="{{ $revenue }}"
                                    data-purchase="{{ $purchase }}" data-expense="{{ $expense }}"
                                    data-label1="{{ __('db.Purchase') }}" data-label2="{{ __('db.revenue') }}"
                                    data-label3="{{ __('db.Expense') }}" width="100" height="95"> </canvas>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                @php
                    $yearly_report = $role_has_permissions_list->where('name', 'yearly_report')->first();
                @endphp
                @if ($yearly_report)
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <h4>{{ __('db.yearly report') }}</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="saleChart" data-sale_chart_value = "{{ json_encode($yearly_sale_amount) }}"
                                    data-purchase_chart_value = "{{ json_encode($yearly_purchase_amount) }}"
                                    data-label1="{{ __('db.Purchased Amount') }}"
                                    data-label2="{{ __('db.Sold Amount') }}"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>{{ __('db.Recent Transaction') }}</h4>
                            <div class="right-column">
                                <div class="badge badge-primary">{{ __('db.latest') }} 5</div>
                            </div>
                        </div>
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="#sale-latest" role="tab"
                                    data-toggle="tab">{{ __('db.Sale') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#purchase-latest" role="tab"
                                    data-toggle="tab">{{ __('db.Purchase') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#quotation-latest" role="tab"
                                    data-toggle="tab">{{ __('db.Quotation') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#payment-latest" role="tab"
                                    data-toggle="tab">{{ __('db.Payment') }}</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade show active" id="sale-latest">
                                <div class="table-responsive">
                                    <table id="recent-sale" class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('db.date') }}</th>
                                                <th>{{ __('db.reference') }}</th>
                                                <th>{{ __('db.customer') }}</th>
                                                <th>{{ __('db.status') }}</th>
                                                <th>{{ __('db.grand total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="purchase-latest">
                                <div class="table-responsive">
                                    <table id="recent-purchase" class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('db.date') }}</th>
                                                <th>{{ __('db.reference') }}</th>
                                                <th>{{ __('db.Supplier') }}</th>
                                                <th>{{ __('db.status') }}</th>
                                                <th>{{ __('db.grand total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="quotation-latest">
                                <div class="table-responsive">
                                    <table id="recent-quotation" class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('db.date') }}</th>
                                                <th>{{ __('db.reference') }}</th>
                                                <th>{{ __('db.customer') }}</th>
                                                <th>{{ __('db.status') }}</th>
                                                <th>{{ __('db.grand total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="payment-latest">
                                <div class="table-responsive">
                                    <table id="recent-payment" class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('db.date') }}</th>
                                                <th>{{ __('db.reference') }}</th>
                                                <th>{{ __('db.Amount') }}</th>
                                                <th>{{ __('db.Paid By') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>{{ __('db.Best Seller') . ' ' . date('F') }}</h4>
                            <div class="right-column">
                                <div class="badge badge-primary">{{ __('db.top') }} 5</div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="monthly-best-selling-qty" class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('db.Product Details') }}</th>
                                        <th>{{ __('db.qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>{{ __('db.Best Seller') . ' ' . date('Y') . '(' . __('db.qty') . ')' }}</h4>
                            <div class="right-column">
                                <div class="badge badge-primary">{{ __('db.top') }} 5</div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="yearly-best-selling-qty" class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('db.Product Details') }}</th>
                                        <th>{{ __('db.qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>{{ __('db.Best Seller') . ' ' . date('Y') . '(' . __('db.Price') . ')' }}</h4>
                            <div class="right-column">
                                <div class="badge badge-primary">{{ __('db.top') }} 5</div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="yearly-best-selling-price" class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('db.Product Details') }}</th>
                                        <th>{{ __('db.grand total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>{{ __('Total Customer Due') ?? 'Top Customer Dues' }}</h4>
                            <div class="right-column">
                                <div class="badge badge-primary">{{ __('db.top') }} 5</div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="top-customer-dues-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('db.customer') }}</th>
                                        <th class="text-right">{{ __('amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="top-customer-dues-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>{{ __('Total Supplier Due') ?? 'Top Supplier Dues' }}</h4>
                            <div class="right-column">
                                <div class="badge badge-primary">{{ __('db.top') }} 5</div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="top-supplier-dues-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('supplier') ?? 'Supplier' }}</th>
                                        <th class="text-right">{{ __('amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="top-supplier-dues-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection

@include('backend.customer.add_modal')

@push('scripts')
    <script>
        $(document).ready(function() {
            // Fix modal stacking so addCustomer appears above Quick Invoice modal
            $('#addCustomer').on('show.bs.modal', function () {
                $(this).css('z-index', 1060);
                setTimeout(function() {
                    $('.modal-backdrop').last().css('z-index', 1055);
                }, 100);
            });
        });
    </script>

    <script type="text/javascript" src="{{ asset($asset_prefix . 'vendor/chart.js/Chart.min.js'); }}"></script>
    <script type="text/javascript" src="{{ asset($asset_prefix . 'js/charts-custom.js'); }}"></script>

    <script type="text/javascript">
        let staff_warehouse_id = @json(auth()->user()->warehouse_id);
        let staff_role_id = @json(auth()->user()->role_id);

        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/yearly-best-selling-price') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var url = '{{ url('/images/product') }}';
                    data.forEach(function(item) {
                        if (item.product_images)
                            var images = item.product_images.split(',');
                        else
                            var images = ['zummXD2dvAtI.png'];
                        $('#yearly-best-selling-price').find('tbody').append(
                            '<tr><td><div class="d-flex align-items-center"><img src="' +
                            url + '/' + images[0] +
                            '" width="30" height="25" class="ml-3 mr-3"> ' + item
                            .product_name + ' [' + item.product_code + ']</div></td><td>' +
                            formatCurrency(item.total_price / item.exchange_rate) + '</td></tr>');
                    })
                }
            });
        });

        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/yearly-best-selling-qty') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var url = '{{ url('/images/product') }}';
                    data.forEach(function(item) {
                        if (item.product_images)
                            var images = item.product_images.split(',');
                        else
                            var images = ['zummXD2dvAtI.png'];
                        $('#yearly-best-selling-qty').find('tbody').append(
                            '<tr><td><div class="d-flex align-items-center"><img src="' +
                            url + '/' + images[0] +
                            '" width="30" height="25" class="ml-3 mr-3"> ' + item
                            .product_name + ' [' + item.product_code + ']</div></td><td>' +
                            item.sold_qty + '</td></tr>');
                    })
                }
            });
        });

        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/monthly-best-selling-qty') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var url = '{{ url('/images/product') }}';
                    data.forEach(function(item) {
                        if (item.product_images)
                            var images = item.product_images.split(',');
                        else
                            var images = ['zummXD2dvAtI.png'];
                        $('#monthly-best-selling-qty').find('tbody').append(
                            '<tr><td><div class="d-flex align-items-center"><img src="' +
                            url + '/' + images[0] +
                            '" width="30" height="25" class="ml-3 mr-3"> ' + item
                            .product_name + ' [' + item.product_code + ']</div></td><td>' +
                            item.sold_qty + '</td></tr>');
                    })
                }
            });
        });

        $(document).ready(function() {
            $.ajax({
                url: "{{ url('/recent-sale') }}",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    data.forEach(function(item) {
                        var sale_date = dateFormat(item.created_at.split('T')[0],
                            '{{ gen_setting()?->date_format ?? 'Y-m-d' }}')
                        if (item.sale_status == 1) {
                            var status =
                                '<div class="badge badge-success">{{ __('db.Completed') }}</div>';
                        } else if (item.sale_status == 2) {
                            var status =
                                '<div class="badge badge-danger">{{ __('db.Pending') }}</div>';
                        } else {
                            var status =
                                '<div class="badge badge-warning">{{ __('db.Draft') }}</div>';
                        }
                        $('#recent-sale').find('tbody').append('<tr><td>' + sale_date +
                            '</td><td>' + item.reference_no + '</td><td>' + item.name +
                            '</td><td>' + status + '</td><td>' + formatCurrency(item.grand_total/item.exchange_rate).toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</td></tr>');
                    })
                }
            });
        });

        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/recent-purchase') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    data.forEach(function(item) {
                        var payment_date = dateFormat(item.created_at.split('T')[0],
                            '{{ gen_setting()?->date_format ?? 'Y-m-d' }}')
                        if (item.status == 1) {
                            var status =
                                '<div class="badge badge-success">{{ __('db.Recieved') }}</div>';
                        } else if (item.status == 2) {
                            var status =
                                '<div class="badge badge-danger">{{ __('db.Partial') }}</div>';
                        } else if (item.status == 3) {
                            var status =
                                '<div class="badge badge-danger">{{ __('db.Pending') }}</div>';
                        } else {
                            var status =
                                '<div class="badge badge-warning">{{ __('db.Ordered') }}</div>';
                        }
                        $('#recent-purchase').find('tbody').append('<tr><td>' + payment_date +
                            '</td><td>' + item.reference_no + '</td><td>' + item.name +
                            '</td><td>' + status + '</td><td>' + formatCurrency(item.grand_total/item.exchange_rate).toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</td></tr>');
                    })
                }
            });
        });

        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/recent-quotation') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    data.forEach(function(item) {
                        var quotation_date = dateFormat(item.created_at.split('T')[0],
                            '{{ gen_setting()?->date_format ?? 'Y-m-d' }}')
                        if (item.quotation_status == 1) {
                            var status =
                                '<div class="badge badge-success">{{ __('db.Pending') }}</div>';
                        } else if (item.quotation_status == 2) {
                            var status =
                                '<div class="badge badge-danger">{{ __('db.Sent') }}</div>';
                        }
                        $('#recent-quotation').find('tbody').append('<tr><td>' +
                            quotation_date + '</td><td>' + item.reference_no + '</td><td>' +
                            item.name + '</td><td>' + status + '</td><td>' + formatCurrency(item
                            .grand_total).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") +
                            '</td></tr>');
                    })
                }
            });
        });

        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/recent-payment') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    data.forEach(function(item) {
                        var payment_date = dateFormat(item.created_at.split('T')[0],
                            '{{ gen_setting()?->date_format ?? 'Y-m-d' }}')
                        $('#recent-payment').find('tbody').append('<tr><td>' + payment_date +
                            '</td><td>' + item.payment_reference + '</td><td>' + formatCurrency(item.amount/item.exchange_rate)
                            .toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") +
                            '</td><td>' + item.paying_method + '</td></tr>');
                    })
                }
            });
        });

        function dateFormat(inputDate, format) {
            const date = new Date(inputDate);
            //extract the parts of the date
            const day = date.getDate();
            const month = date.getMonth() + 1;
            const year = date.getFullYear();
            //replace the month
            format = format.replace("m", month.toString().padStart(2, "0"));
            //replace the year
            format = format.replace("Y", year.toString());
            //replace the day
            format = format.replace("d", day.toString().padStart(2, "0"));
            return format;
        }


        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#userShowModal').modal('show');
                    $('#user-id').text(data.id);
                    $('#user-name').text(data.name);
                    $('#user-email').text(data.email);
                }
            });
        })
        // Show and hide color-switcher
        $(".color-switcher .switcher-button").on('click', function() {
            $(".color-switcher").toggleClass("show-color-switcher", "hide-color-switcher", 300);
        });

        // Color Skins
        $('a.color').on('click', function() {
            /*var title = $(this).attr('title');
            $('#style-colors').attr('href', 'css/skin-' + title + '.css');
            return false;*/
            $.get('setting/general_setting/change-theme/' + $(this).data('color'), function(data) {});
            var style_link = $('#custom-style').attr('href').replace(/([^-]*)$/, $(this).data('color'));
            $('#custom-style').attr('href', style_link);
        });

        $(".date-btn").on("click", function() {
            $(".date-btn").removeClass("active");
            $(this).addClass("active");
            var start_date = $(this).data('start_date');
            var end_date = $(this).data('end_date');
            var warehouse_id = $("#warehouse_btn").val();
            console.log(warehouse_id);
            $.get('dashboard-filter/' + start_date + '/' + end_date + '/' + warehouse_id, function(data) {
                dashboardFilter(data);
            });
        });

        $("#warehouse_btn").on("change", function() {
            var warehouse_id = $(this).val();
            var start_date = $('input[name="start_date"]').val();
            var end_date = $('input[name="end_date"]').val();

            $.get('dashboard-filter/' + start_date + '/' + end_date + '/' + warehouse_id, function(data) {
                dashboardFilter(data);
            });
        });

        function dashboardFilter(data) {
            // data is an array:
            // [revenue, sale_return, profit, purchase_return, total_sale, invoice_due, total_purchase, purchase_due]

            $('.total_sale-data').hide();
            $('.total_sale-data').html(formatCurrency(parseFloat(data[4] ?? 0)));
            $('.total_sale-data').show(500);

            $('.revenue-data').hide();
            $('.revenue-data').html(formatCurrency(parseFloat(data[0] ?? 0)));
            $('.revenue-data').show(500);

            $('.invoice-due-data').hide();
            $('.invoice-due-data').html(formatCurrency(parseFloat(data[5] ?? 0)));
            $('.invoice-due-data').show(500);

            $('.return-data').hide();
            $('.return-data').html(formatCurrency(parseFloat(data[1] ?? 0)));
            $('.return-data').show(500);

            $('.total_purchase-data').hide();
            $('.total_purchase-data').html(formatCurrency(data[6] ?? 0));
            $('.total_purchase-data').show(500);

            $('.purchase_due-data').hide();
            $('.purchase_due-data').html(formatCurrency(data[7] ?? 0));
            $('.purchase_due-data').show(500);

            $('.expense-data').hide();
            $('.expense-data').html(formatCurrency(data[8] ?? 0));
            $('.expense-data').show(500);

            $('.purchase_return-data').hide();
            $('.purchase_return-data').html(formatCurrency(data[3] ?? 0));
            $('.purchase_return-data').show(500);

            $('.profit-data').hide();
            $('.profit-data').html(formatCurrency(data[2] ?? 0));
            $('.profit-data').show(500);

            $('.stock-value-data').hide();
            $('.stock-value-data').html(formatCurrency(data[9] ?? 0));
            $('.stock-value-data').show(500);

            $('.stock-alert-data').hide();
            $('.stock-alert-data').html(data[10] ?? 0);
            $('.stock-alert-data').show(500);

            $('.payment-daily-data').hide();
            $('.payment-daily-data').html(formatCurrency(data[11] ?? 0));
            $('.payment-daily-data').show(500);

            $('.payment-monthly-data').hide();
            $('.payment-monthly-data').html(formatCurrency(data[12] ?? 0));
            $('.payment-monthly-data').show(500);

            // Build Due Lists
            if(data[13] && data[13].length > 0) {
                let custHtml = '';
                data[13].forEach(function(item) {
                    custHtml += '<tr><td>' + item.name + '<br><small>' + (item.phone_number ?? '') + '</small></td><td class="text-right text-danger">' + formatCurrency(item.total_due) + '</td></tr>';
                });
                $('#top-customer-dues-body').html(custHtml);
            } else {
                $('#top-customer-dues-body').html('<tr><td colspan="2" class="text-center">No Dues</td></tr>');
            }

            if(data[14] && data[14].length > 0) {
                let suppHtml = '';
                data[14].forEach(function(item) {
                    suppHtml += '<tr><td>' + item.name + '<br><small>' + (item.phone_number ?? '') + '</small></td><td class="text-right text-danger">' + formatCurrency(item.total_due) + '</td></tr>';
                });
                $('#top-supplier-dues-body').html(suppHtml);
            } else {
                $('#top-supplier-dues-body').html('<tr><td colspan="2" class="text-center">No Dues</td></tr>');
            }
        }

        $(function () {

            var start = moment().subtract(29, 'days');
            var end = moment();

            // Override initial start/end ONLY once (page load)
            // @if(isset($start_date))
            //     start = moment("{{ $start_date }}", 'YYYY-MM-DD');
            // @endif

            // @if(isset($end_date))
            //     end = moment("{{ $end_date }}", 'YYYY-MM-DD');
            // @endif

            function applyDashboardFilter(start, end) {

                var start_date = start.format('YYYY-MM-DD');
                var end_date = end.format('YYYY-MM-DD');

                // visible field
                $('.daterangepicker-field').val(start_date + ' To ' + end_date);

                // hidden fields (NOW SAFE)
                $('input[name="start_date"]').val(start_date);
                $('input[name="end_date"]').val(end_date);

                // console.log(start_date+' '+end_date);

                $(".date-btn").removeClass("active");

                var warehouse_id = $("#warehouse_btn").val();
                if (warehouse_id === undefined || staff_role_id > 2) {
                    console.log(start_date, end_date);
                    warehouse_id = staff_warehouse_id;
                }

                $.get('dashboard-filter/' + start_date + '/' + end_date + '/' + warehouse_id, function (data) {
                    dashboardFilter(data);
                });
            }

            // 🔴 THIS is the important part
            $('.daterangepicker-field').on('apply.daterangepicker', function (ev, picker) {
                applyDashboardFilter(picker.startDate, picker.endDate);
            });

            // initial dashboard load
            applyDashboardFilter(start, end);

        });

        // Quick Invoice Modal Logic
        $(document).ready(function() {
            let qiRowIndex = 0;

            function addQiRow() {
                qiRowIndex++;
                let newRow = `
                <tr id="qi-row-${qiRowIndex}" style="font-size: 0.9rem;">
                    <td class="align-middle px-1 py-1 text-center">${qiRowIndex}</td>
                    <td class="align-middle px-1 py-1">
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm qi-product-search" placeholder="Search Product..." autocomplete="off">
                            <div class="qi-product-dropdown dropdown-menu w-100" style="display:none; max-height: 200px; overflow-y: auto; font-size: 0.9rem;"></div>
                        </div>
                        <input type="hidden" class="qi-product-id" name="product_id[]">
                        <input type="hidden" class="qi-product-code" name="product_code[]">
                        <input type="hidden" name="product_batch_id[]" value="">
                        <input type="hidden" name="imei_number[]" value="">
                        <input type="hidden" name="discount[]" value="0">
                        <input type="hidden" name="tax_rate[]" value="0">
                        <input type="hidden" name="tax[]" value="0">
                        <input type="hidden" name="sale_unit[]" class="qi-sale-unit-id" value="">
                        <input type="hidden" name="net_unit_price[]" class="qi-net-unit-price" value="0">
                    </td>
                    <td class="align-middle px-1 py-1"><input type="text" class="form-control form-control-sm" name="description[]"></td>
                    <td class="align-middle px-1 py-1"><input type="number" class="form-control form-control-sm qi-qty" name="qty[]" value="1" min="1" step="any"></td>
                    <td class="align-middle px-1 py-1"><input type="number" class="form-control form-control-sm qi-rate" name="product_price[]" value="0" step="any" readonly></td>
                    <td class="align-middle px-1 py-1"><input type="text" class="form-control form-control-sm qi-subtotal" name="subtotal[]" value="0" readonly></td>
                    <td class="align-middle px-1 py-1 text-center"><button type="button" class="btn btn-danger btn-sm qi-remove-row py-1 px-2"><i class="ti ti-trash"></i></button></td>
                </tr>
                `;
                $('#qi-order-table tbody').append(newRow);
            }

            // Initialize with one row
            addQiRow();

            $('#qi-add-row-btn').on('click', function() {
                addQiRow();
            });

            $(document).on('click', '.qi-remove-row', function() {
                if ($('#qi-order-table tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateQiTotals();
                } else {
                    alert('You must have at least one product row.');
                }
            });

            // Basic product search simulation
            $(document).on('keyup', '.qi-product-search', function() {
                let input = $(this);
                let dropdown = input.siblings('.qi-product-dropdown');
                let term = input.val();
                
                if (term.length < 2) {
                    dropdown.hide();
                    return;
                }

                // Call sales/search
                $.ajax({
                    type: 'GET',
                    url: '{{url("/sales/search")}}',
                    data: {
                        warehouse_id: 1, // Default warehouse
                        search: term
                    },
                    success: function(data) {
                        dropdown.empty();
                        if(data.length > 0) {
                            data.forEach(function(item) {
                                let label = item.name + ' (' + item.code + ')';
                                let code = item.code;
                                dropdown.append(`<a href="#" class="dropdown-item qi-product-option" data-code="${code}">${label}</a>`);
                            });
                            dropdown.show();
                        } else {
                            dropdown.hide();
                        }
                    }
                });
            });

            $(document).on('click', '.qi-product-option', function(e) {
                e.preventDefault();
                let option = $(this);
                let code = option.data('code');
                let label = option.text();
                let row = option.closest('tr');
                
                // Get product details
                $.ajax({
                    type: 'GET',
                    url: '{{url("sales/lims_product_search")}}',
                    data: {
                        data: {
                            code: code,
                            customer_id: 1, // Walkin customer
                            qty: 1,
                            embedded: 0,
                            batch: '',
                            pre_qty: 0,
                            price: '',
                            imei: ''
                        }
                    },
                    success: function(data) {
                        row.find('.qi-product-search').val(label);
                        row.find('.qi-product-dropdown').hide();
                        
                        // Populate hidden inputs
                        row.find('.qi-product-id').val(data.id || data[0] || data.product_id); // Ensure correct key is used
                        row.find('.qi-product-code').val(code);
                        let price = data.price !== undefined ? data.price : (data[2] || 0); 
                        row.find('.qi-rate').val(price);
                        row.find('.qi-net-unit-price').val(price);
                        
                        let saleUnit = '';
                        if (data.unit_name) {
                            saleUnit = data.unit_name.split(',')[0];
                        }
                        row.find('.qi-sale-unit-id').val(saleUnit);
                        
                        calculateQiTotals();
                    }
                });
            });

            // Hide dropdown when clicking outside
            $(document).on('click', function (e) {
                if ($(e.target).closest(".position-relative").length === 0) {
                    $(".qi-product-dropdown").hide();
                }
            });

            $(document).on('input', '.qi-qty', function() {
                calculateQiTotals();
            });

            function calculateQiTotals() {
                let grandTotal = 0;
                let totalQty = 0;
                let itemCount = 0;

                $('#qi-order-table tbody tr').each(function() {
                    let qty = parseFloat($(this).find('.qi-qty').val()) || 0;
                    let rate = parseFloat($(this).find('.qi-rate').val()) || 0;
                    let subtotal = qty * rate;
                    
                    $(this).find('.qi-subtotal').val(subtotal.toFixed(2));
                    
                    if (qty > 0 && rate > 0) {
                        grandTotal += subtotal;
                        totalQty += qty;
                        itemCount++;
                    }
                });

                $('#qi-total-display').text(grandTotal.toFixed(2));
                $('#qi_grand_total').val(grandTotal.toFixed(2));
                $('#qi_total_price').val(grandTotal.toFixed(2));
                $('#qi_total_qty').val(totalQty);
                $('#qi_item').val(itemCount);
                $('#qi_paid_amount').val(grandTotal.toFixed(2)); // Default to fully paid
                $('#qi_paying_amount').val(grandTotal.toFixed(2));
            }

            $('#qi_paid_amount').on('input', function() {
                $('#qi_paying_amount').val($(this).val());
            });

            $('#qi-submit-btn').on('click', function() {
                let form = $('#quick-invoice-form');
                
                // Validate
                if ($('#qi_item').val() == "0") {
                    alert("Please select at least one product.");
                    return;
                }

                // Remove empty rows before submit
                $('#qi-order-table tbody tr').each(function() {
                    let pid = $(this).find('.qi-product-id').val();
                    if (!pid || pid === '') {
                        $(this).remove();
                    }
                });

                if ($('#qi-order-table tbody tr').length === 0) {
                    alert("Please select at least one valid product.");
                    // Re-add an empty row so the table isn't permanently empty
                    addQiRow();
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        alert('Invoice created successfully!');
                        $('#quickInvoiceModal').modal('hide');
                        
                        // Redirect to view/print invoice
                        let sale_id = response.sale_id ? response.sale_id : response;
                        let link = "{{ url('sales/gen_invoice') }}/" + sale_id + "?is_print=true";
                        window.location.href = link;
                    },
                    error: function(xhr) {
                        alert('Error creating invoice. Check console for details.');
                        console.log(xhr.responseText);
                    }
                });
            });
        });

    </script>

    <!-- Quick Invoice Modal -->
    <div id="quickInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="quickInvoiceLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="padding: 5px 1rem;" >
                    <h5 id="quickInvoiceLabel" class="modal-title">{{__('Quick Invoice')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic" style="margin-bottom: 0px; text-align:right"><small>{{__('db.The field labels marked with are required input fields')}} *.</small></p>
                    <form id="quick-invoice-form" action="{{ route('sales.store') }}" method="POST">
                        @csrf
                        <!-- Hidden Fields required by sale store -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('db.customer')}} *</label>
                                    <div class="input-group pos">
                                        @php
                                        $deposit = [];
                                        $points = [];
                                        $customer_active = DB::table('permissions')
                                        ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                                        ->where([
                                            ['permissions.name', 'customers-add'],
                                            ['role_id', \Auth::user()->role_id] ])->first();

                                            $lims_pos_setting_data = App\Models\PosSetting::latest()->first();
                                            $lims_customer_list = App\Models\Customer::where('is_active', true)->get();

                                            if($lims_pos_setting_data) {
                                                $customer_id = $lims_pos_setting_data->customer_id;
                                            }
                                            else{
                                                $customer_id = $lims_customer_list[0]->id;
                                            }
                                        @endphp
                                        @if($customer_active)
                                        <select required name="customer_id" id="qi_customer_id" class="selectpicker form-control" data-live-search="true" title="Select customer...">
                                        @foreach($lims_customer_list as $customer)
                                            @php
                                            $deposit[$customer->id] = $customer->deposit - $customer->expense;

                                            $points[$customer->id] = $customer->points;
                                            @endphp
                                            <option value="{{$customer->id}}" data-type="{{$customer->type}}" data-credit-limit="{{ $customer->credit_limit }}" data-pay_term_no="{{ $customer->pay_term_no }}" data-pay_term_period="{{ $customer->pay_term_period }}"  @if($customer->id == $customer_id) selected @endif>{{$customer->name}} @if($customer->wa_number)({{$customer->wa_number}})@endif</option>
                                        @endforeach
                                        </select>
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#addCustomer"><i class="ti ti-plus"></i></button>
                                        @else
                                        <select required name="customer_id" id="qi_customer_id" class="selectpicker form-control" data-live-search="true" title="Select customer...">
                                        @foreach($lims_customer_list as $customer)
                                            @php
                                            $deposit[$customer->id] = $customer->deposit - $customer->expense;

                                            $points[$customer->id] = $customer->points;
                                            @endphp
                                            <option value="{{$customer->id}}" data-type="{{$customer->type}}" data-credit-limit="{{ $customer->credit_limit }}" data-pay_term_no="{{ $customer->pay_term_no }}" data-pay_term_period="{{ $customer->pay_term_period }}"  @if($customer->id == $customer_id) selected @endif>{{$customer->name . ' (' . $customer->phone_number . ')'}}</option>
                                        @endforeach
                                        </select>
                                        @endif
                                        <x-validation-error fieldName="customer_id" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="warehouse_id" value="1">
                        <input type="hidden" name="biller_id" value="1">
                        <input type="hidden" name="exchange_rate" value="1">
                        <input type="hidden" name="currency_id" value="1">
                        <input type="hidden" name="sale_status" value="1">
                        <input type="hidden" name="coupon_active" value="0">
                        <input type="hidden" name="redeem_point" value="0">
                        <input type="hidden" name="draft" value="0">
                        <input type="hidden" name="item" id="qi_item" value="0">
                        <input type="hidden" name="total_qty" id="qi_total_qty" value="0">
                        <input type="hidden" name="total_discount" value="0">
                        <input type="hidden" name="total_tax" value="0">
                        <input type="hidden" name="total_price" id="qi_total_price" value="0">
                        <input type="hidden" name="grand_total" id="qi_grand_total" value="0">
                        <input type="hidden" name="order_tax_rate" value="0">
                        <input type="hidden" name="order_tax" value="0">
                        <input type="hidden" name="order_discount" value="0">
                        <input type="hidden" name="shipping_cost" value="0">
                        <input type="hidden" name="payment_status" value="4"> 

                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-bordered table-hover table-sm" id="qi-order-table" style="font-size: 0.9rem;">
                                <thead class="bg-white" style="position: sticky; top: 0; z-index: 1; box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">
                                    <tr>
                                        <th class="px-1 py-2 text-center">#</th>
                                        <th class="px-1 py-2">{{__('db.Product')}} *</th>
                                        <th class="px-1 py-2">{{__('db.Description')}}</th>
                                        <th class="px-1 py-2">{{__('db.Quantity')}} *</th>
                                        <th class="px-1 py-2">{{__('db.Rate')}}</th>
                                        <th class="px-1 py-2">{{__('db.Subtotal')}}</th>
                                        <th class="px-1 py-2 text-center"><i class="ti ti-trash"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Rows added via JS -->
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="qi-add-row-btn"><i class="ti ti-plus"></i> Add line</button>

                        <div class="row mt-2">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="text-right py-1 align-middle"><strong>Grand Total</strong></td>
                                        <td class="text-right py-1"><span id="qi-total-display" style="font-size: 1.25rem; font-weight: bold;">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-right py-1 align-middle"><strong>Paying Amount *</strong></td>
                                        <td class="py-1">
                                            <input type="number" name="paid_amount[]" class="form-control form-control-sm text-right" value="0" step="any" id="qi_paid_amount" required>
                                            <input type="hidden" name="paying_amount[]" id="qi_paying_amount" value="0">
                                            <input type="hidden" name="payment_note" value="">
                                            <input type="hidden" name="cheque_no" value="">
                                            <input type="hidden" name="card_number" value="">
                                            <input type="hidden" name="card_holder_name" value="">
                                            <input type="hidden" name="card_type" value="">
                                            <input type="hidden" name="gift_card_id" value="">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-right py-1 align-middle"><strong>Paying Method *</strong></td>
                                        <td class="py-1">
                                            <select name="paid_by_id[]" class="form-control form-control-sm">
                                                <option value="1">Cash</option>
                                                <option value="3">Credit Card</option>
                                                <option value="4">Cheque</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="form-group mt-2 mb-0 text-right">
                            <button type="button" class="btn btn-sm px-5" style="background-color: #7c5cc4; border-color: #7c5cc4; color: white; padding-top: 0.4rem; padding-bottom: 0.4rem;" id="qi-submit-btn">{{__('db.Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endpush

