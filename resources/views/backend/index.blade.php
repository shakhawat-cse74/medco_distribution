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

.qi-product-table-wrapper {
    max-height: 240px;
    overflow-y: auto !important;
    overflow-x: auto !important;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f8fafc;
}
.qi-product-table-wrapper::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.qi-product-table-wrapper::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 4px;
}
.qi-product-table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.qi-product-table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #7c5cc4;
}
#qi-order-table thead th {
    position: sticky !important;
    top: 0 !important;
    background-color: #f8fafc !important;
    z-index: 10 !important;
    border-top: none !important;
}
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
            <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center flex-wrap my-3" style="gap: 12px;">
                <div class="brand-text">
                    <h3 style="font-size:1.1rem; margin-bottom: 0;">{{ __('db.welcome') }} <span>{{ Auth::user()->name }}</span></h3>
                </div>
                @if (in_array('restaurant', explode(',', gen_setting()->modules ?? '')))
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
                @if (in_array('restaurant', explode(',', gen_setting()->modules ?? '')) && isset($cooked) && $cooked > 0)
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
                    <div class="dashboard-filter-bar">
                        <div class="dashboard-filters d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" style="gap: 8px;">
                            @if (\Auth::user()->role_id <= 2)
                            {{-- Warehouse --}}
                            <div class="filter-toggle btn-group mt-0" style="border: 1px solid #7c5cc4; border-radius: 5px;">
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
                            <div id="dashboard-datepicker">
                                <div class="input-group input-group-md">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="ti ti-calendar text-primary"></i>
                                        </span>
                                    </div>

                                    <input type="text"
                                        class="daterangepicker-field form-control border-left-0"
                                        placeholder="Select Date" style="padding-left:0;min-width: 180px;height:40px"/>

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
            function applyDashboardFilter(start, end) {

                var start_date = start.format('YYYY-MM-DD');
                var end_date = end.format('YYYY-MM-DD');

                // visible field
                $('.daterangepicker-field').val(start_date + ' To ' + end_date);

                // hidden fields (NOW SAFE)
                $('input[name="start_date"]').val(start_date);
                $('input[name="end_date"]').val(end_date);

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

        // Quick Invoice Modal Logic (Sale / Purchase Search Style)
        $(document).ready(function() {
            let typingTimer;
            const doneTypingInterval = 200;
            const $searchInput = $('#qi_product_search_input');
            const $resultsContainer = $('#qi_product_results_container');
            const $noResults = $('#qi_no_results_message');
            const currencySymbol = '{{ config("currency") ?? "৳" }}';

            function formatCurrency(amount) {
                let decimals = {{ gen_setting()->decimal ?? 2 }};
                return currencySymbol + ' ' + (parseFloat(amount) || 0).toFixed(decimals);
            }

            function clearQiSearchResults() {
                $resultsContainer.empty().hide();
                $noResults.hide();
            }

            function updateQiCustomerAddress() {
                let $opt = $('#qi_customer_id option:selected');
                if ($opt.length && $('#qi_customer_id').val()) {
                    let name = $opt.data('name') || $opt.text();
                    let company = $opt.data('company') || '';
                    let phone = $opt.data('phone') || '';
                    let address = $opt.data('address') || '';
                    let city = $opt.data('city') || '';
                    let state = $opt.data('state') || '';
                    let postal = $opt.data('postal') || '';
                    let country = $opt.data('country') || '';

                    let addrLine1 = address ? address : 'No address provided';
                    let addrLine2 = [city, state, postal].filter(Boolean).join(', ');
                    if (country) addrLine2 += (addrLine2 ? ', ' : '') + country;

                    let billHtml = `<div class="font-weight-bold text-dark">${name} ${company ? '<span class="text-muted font-weight-normal">(' + company + ')</span>' : ''}</div>
                                    <div class="text-muted" style="font-size:11px;">${addrLine1}</div>
                                    ${addrLine2 ? '<div class="text-muted" style="font-size:11px;">' + addrLine2 + '</div>' : ''}
                                    ${phone ? '<div class="text-muted" style="font-size:11px;"><i class="ti ti-phone mr-1"></i>' + phone + '</div>' : ''}`;

                    let shipHtml = `<div class="font-weight-bold text-dark">${name}</div>
                                    <div class="text-muted" style="font-size:11px;">${addrLine1}</div>
                                    ${addrLine2 ? '<div class="text-muted" style="font-size:11px;">' + addrLine2 + '</div>' : ''}
                                    ${phone ? '<div class="text-muted" style="font-size:11px;"><i class="ti ti-phone mr-1"></i>' + phone + '</div>' : ''}`;

                    $('#qi_bill_to_content').html(billHtml);
                    $('#qi_ship_to_content').html(shipHtml);
                } else {
                    $('#qi_bill_to_content').html('<span class="text-muted">Select customer to view billing address</span>');
                    $('#qi_ship_to_content').html('<span class="text-muted">Select customer to view shipping address</span>');
                }
            }

            $('#qi_customer_id').on('change', function() {
                updateQiCustomerAddress();
            });

            // Initial address load
            updateQiCustomerAddress();

            let currentQiSearchXhr = null;

            function searchQiProducts(searchTerm) {
                searchTerm = (searchTerm || '').trim();
                if (searchTerm.length === 0) {
                    if (currentQiSearchXhr) {
                        currentQiSearchXhr.abort();
                    }
                    clearQiSearchResults();
                    return;
                }

                if (currentQiSearchXhr) {
                    currentQiSearchXhr.abort();
                }

                let warehouse_id = $('#qi_warehouse_id').val() || 1;
                $noResults.hide();

                currentQiSearchXhr = $.ajax({
                    url: '{{ url("/sales/search") }}',
                    type: 'GET',
                    data: {
                        warehouse_id: warehouse_id,
                        search: searchTerm
                    },
                    success: function(data) {
                        $resultsContainer.empty();
                        if (data && data.length > 0) {
                            $noResults.hide();
                            data.forEach(function(product) {
                                let stockVal = product.qty !== undefined ? product.qty : 0;
                                let priceVal = product.price !== undefined ? parseFloat(product.price).toFixed(2) : '0.00';
                                let batch_id = product.product_batch_id || '';
                                let imei_no = product.imei_number || '';

                                let productHtml = '';

                                if (product.is_imei == '1' || product.is_imei === 1 || product.is_imei === true) {
                                    if (imei_no !== null && $.trim(imei_no) !== '') {
                                        productHtml = `
                                            <div class="qi-product-item p-2 border-bottom d-flex justify-content-between align-items-center" 
                                                 style="cursor: pointer; transition: background 0.15s;"
                                                 data-id="${product.id}"
                                                 data-code="${product.code}"
                                                 data-name="${product.name}"
                                                 data-qty="${stockVal}"
                                                 data-price="${product.price}"
                                                 data-batch="${batch_id}"
                                                 data-imei="${imei_no}"
                                                 data-type="${product.type}">
                                                <div>
                                                    <div class="font-weight-bold text-dark" style="font-size: 14px;">${product.name}</div>
                                                    <small class="text-muted"><i class="ti ti-barcode mr-1"></i>${product.code}</small>
                                                    <span class="badge badge-info ml-1">IMEI: ${imei_no}</span>
                                                </div>
                                                <div class="text-right ml-3">
                                                    <div class="font-weight-bold text-primary" style="font-size: 14px;">${formatCurrency(priceVal)}</div>
                                                </div>
                                            </div>`;
                                    }
                                } else if (product.product_batch_id != null) {
                                    let expired = product.expired_date == 0 ? "expired" : (product.expired_date || '');
                                    productHtml = `
                                        <div class="qi-product-item p-2 border-bottom d-flex justify-content-between align-items-center" 
                                             style="cursor: pointer; transition: background 0.15s;"
                                             data-id="${product.id}"
                                             data-code="${product.code}"
                                             data-name="${product.name}"
                                             data-qty="${stockVal}"
                                             data-price="${product.price}"
                                             data-batch="${batch_id}"
                                             data-imei=""
                                             data-type="${product.type}">
                                            <div>
                                                <div class="font-weight-bold text-dark" style="font-size: 14px;">${product.name}</div>
                                                <small class="text-muted"><i class="ti ti-barcode mr-1"></i>${product.code}</small>
                                                ${expired ? '<span class="badge badge-warning ml-1">'+expired+'</span>' : ''}
                                            </div>
                                            <div class="text-right ml-3">
                                                <div class="font-weight-bold text-primary" style="font-size: 14px;">${formatCurrency(priceVal)}</div>
                                            </div>
                                        </div>`;
                                } else {
                                    productHtml = `
                                        <div class="qi-product-item p-2 border-bottom d-flex justify-content-between align-items-center" 
                                             style="cursor: pointer; transition: background 0.15s;"
                                             data-id="${product.id}"
                                             data-code="${product.code}"
                                             data-name="${product.name}"
                                             data-qty="${stockVal}"
                                             data-price="${product.price}"
                                             data-batch="${batch_id}"
                                             data-imei=""
                                             data-type="${product.type}">
                                            <div>
                                                <div class="font-weight-bold text-dark" style="font-size: 14px;">${product.name}</div>
                                                <small class="text-muted"><i class="ti ti-barcode mr-1"></i>${product.code}</small>
                                            </div>
                                            <div class="text-right ml-3">
                                                <div class="font-weight-bold text-primary" style="font-size: 14px;">${formatCurrency(priceVal)}</div>
                                            </div>
                                        </div>`;
                                }

                                if (productHtml) {
                                    $resultsContainer.append(productHtml);
                                }
                            });
                            $resultsContainer.show();
                        } else {
                            $resultsContainer.hide();
                            $noResults.show();
                        }
                    },
                    error: function(xhr, status) {
                        if (status !== 'abort') {
                            $resultsContainer.hide();
                            $noResults.show();
                        }
                    }
                });
            }

            // Real-time instant search on input & paste (Same speed as Add Sale)
            $searchInput.on('input', function() {
                let val = $(this).val().trim();
                if (val.length >= 1) {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(function() {
                        searchQiProducts(val);
                    }, 50);
                } else {
                    clearQiSearchResults();
                }
            });

            $searchInput.on('paste', function(e) {
                const pastedData = (e.originalEvent || e).clipboardData ? (e.originalEvent || e).clipboardData.getData('text') : '';
                if (pastedData && pastedData.trim().length >= 1) {
                    searchQiProducts(pastedData.trim());
                }
            });

            // Close results dropdown on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#qi_product_results_container, #qi_product_search_input').length) {
                    clearQiSearchResults();
                }
            });

            // Hover effect on product items
            $(document).on('mouseenter', '.qi-product-item', function() {
                $(this).css('background-color', '#f4f0ff');
            }).on('mouseleave', '.qi-product-item', function() {
                $(this).css('background-color', '#ffffff');
            });

            let currentQiEditRow = null;

            // When product item is clicked -> Add to invoice table!
            $(document).on('click', '.qi-product-item', function() {
                let id = $(this).data('id');
                let code = $(this).data('code');
                let name = $(this).data('name');
                let price = parseFloat($(this).data('price')) || 0;
                let stock = parseFloat($(this).data('qty')) || 0;
                let batch = $(this).data('batch') || '';
                let imei = $(this).data('imei') || '';
                let type = $(this).data('type') || 'standard';

                // Check if product already exists in table
                let existingRow = null;
                $('#qi-order-table tbody tr').each(function() {
                    if ($(this).find('.qi-product-id').val() == id) {
                        existingRow = $(this);
                        return false;
                    }
                });

                if (existingRow) {
                    let currentQty = parseFloat(existingRow.find('.qi-qty').val()) || 0;
                    existingRow.find('.qi-qty').val(currentQty + 1);
                    if (imei) {
                        let curImeis = existingRow.find('.qi-imei-number').val();
                        if (curImeis && !curImeis.split(',').includes(imei)) {
                            existingRow.find('.qi-imei-number').val(curImeis + ',' + imei);
                        }
                    }
                } else {
                    let rowCount = $('#qi-order-table tbody tr').length + 1;
                    let newRow = `
                    <tr style="font-size: 0.9rem;" 
                        data-name="${name}" 
                        data-code="${code}" 
                        data-product-type="${type}"
                        data-cost-default="0" 
                        data-cost-lowest="0" 
                        data-cost-avg="0" 
                        data-cost-highest="0"
                        data-retail-price="${price}"
                        data-wholesale-price="0"
                        data-units-name=""
                        data-units-operator=""
                        data-units-operation-value="">
                        <td class="align-middle px-1 py-1 text-center font-weight-bold text-muted qi-row-num">${rowCount}</td>
                        <td class="align-middle px-2 py-1" style="width: 115px;">
                            <span class="badge badge-light border font-weight-bold text-dark px-2 py-1" style="font-size: 11px;">${code}</span>
                            <input type="hidden" class="qi-product-id" name="product_id[]" value="${id}">
                            <input type="hidden" class="qi-product-code" name="product_code[]" value="${code}">
                            <input type="hidden" class="qi-product-batch-id" name="product_batch_id[]" value="${batch}">
                            <input type="hidden" class="qi-imei-number" name="imei_number[]" value="${imei}">
                            <input type="hidden" class="qi-tax-rate" name="tax_rate[]" value="0">
                            <input type="hidden" name="sale_unit[]" class="qi-sale-unit-id" value="">
                            <input type="hidden" name="net_unit_price[]" class="qi-net-unit-price" value="${price.toFixed(2)}">
                            <input type="hidden" name="total[]" class="qi-line-total" value="${price.toFixed(2)}">
                        </td>
                        <td class="align-middle px-2 py-1">
                            <div class="font-weight-bold text-dark qi-clickable-name" style="cursor: pointer;" title="Click to view purchase costs & edit product">
                                ${name} <i class="ti ti-edit text-primary ml-1" style="font-size:11px;"></i>
                            </div>
                        </td>
                        <td class="align-middle px-1 py-1" style="width: 95px;">
                            <input type="number" class="form-control form-control-sm text-right qi-qty font-weight-bold" name="qty[]" value="1" min="0.01" step="any">
                        </td>
                        <td class="align-middle px-1 py-1 text-center" style="width: 70px;">
                            <span class="badge badge-secondary px-2 py-1 qi-unit-display" style="font-size: 11px;">PC</span>
                        </td>
                        <td class="align-middle px-1 py-1" style="width: 110px;">
                            <input type="number" class="form-control form-control-sm text-right qi-rate font-weight-bold" name="product_price[]" value="${price.toFixed(2)}" step="any">
                        </td>
                        <td class="align-middle px-1 py-1" style="width: 90px;">
                            <input type="number" class="form-control form-control-sm text-right qi-discount font-weight-bold" name="discount[]" value="0" min="0" step="any">
                        </td>
                        <td class="align-middle px-1 py-1" style="width: 80px;">
                            <input type="text" class="form-control form-control-sm text-right qi-tax font-weight-bold bg-light" name="tax[]" value="0.00" readonly>
                        </td>
                        <td class="align-middle px-1 py-1" style="width: 115px;">
                            <input type="text" class="form-control form-control-sm text-right qi-subtotal font-weight-bold bg-light" name="subtotal[]" value="${price.toFixed(2)}" readonly>
                        </td>
                        <td class="align-middle px-1 py-1 text-center" style="width: 75px;">
                            <button type="button" class="btn btn-outline-primary btn-sm qi-edit-row py-1 px-1 mr-1" title="View Purchase Costs & Edit Product"><i class="ti ti-edit"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-sm qi-remove-row py-1 px-1"><i class="ti ti-trash"></i></button>
                        </td>
                    </tr>`;
                    $('#qi-order-table tbody').append(newRow);

                    // Fetch sale unit, tax, discount & purchase cost insights in background
                    let addedRow = $('#qi-order-table tbody tr:last');
                    $.ajax({
                        type: 'GET',
                        url: '{{url("sales/lims_product_search")}}',
                        data: {
                            data: {
                                code: code,
                                customer_id: $('#qi_customer_id').val() || 1,
                                qty: 1,
                                embedded: 0,
                                batch: batch,
                                pre_qty: 0,
                                price: price,
                                imei: imei
                            }
                        },
                        success: function(data) {
                            if (data) {
                                if (data.unit_name) {
                                    let baseUnit = data.unit_name.split(',')[0];
                                    addedRow.find('.qi-sale-unit-id').val(baseUnit);
                                    addedRow.find('.qi-unit-display').text(baseUnit);
                                    addedRow.attr('data-units-name', data.unit_name);
                                    addedRow.attr('data-units-operator', data.unit_operator || '');
                                    addedRow.attr('data-units-operation-value', data.unit_operation_value || '');
                                }
                                if (data.tax_rate) {
                                    addedRow.find('.qi-tax-rate').val(data.tax_rate);
                                }
                                if (data.discount && data.discount > 0) {
                                    addedRow.find('.qi-discount').val(parseFloat(data.discount).toFixed(2));
                                }
                                if (data.wholesale_price) {
                                    addedRow.attr('data-wholesale-price', data.wholesale_price);
                                }
                                addedRow.attr('data-product-type', data.type || 'standard');
                                addedRow.attr('data-retail-price', data.price || price);
                                addedRow.attr('data-cost-default', data.cost || 0);
                                addedRow.attr('data-cost-lowest', (data.cost_lowest !== undefined ? data.cost_lowest : data.cost) || 0);
                                addedRow.attr('data-cost-avg', (data.cost_avg !== undefined ? data.cost_avg : data.cost) || 0);
                                addedRow.attr('data-cost-highest', (data.cost_highest !== undefined ? data.cost_highest : data.cost) || 0);
                                calculateQiTotals();
                            }
                        }
                    });
                }

                clearQiSearchResults();
                $searchInput.val('').focus();
                calculateQiTotals();
            });

            // When price option changes inside Edit Modal
            $('#qi_modal_price_option').on('change', function() {
                $('#qi_modal_price').val($(this).val());
            });

            // Open Product Edit Modal on click of edit button or product title
            $(document).on('click', '.qi-edit-row, .qi-clickable-name', function() {
                currentQiEditRow = $(this).closest('tr');
                let name = currentQiEditRow.attr('data-name') || '';
                let code = currentQiEditRow.attr('data-code') || '';
                let type = currentQiEditRow.attr('data-product-type') || 'standard';
                let qty = parseFloat(currentQiEditRow.find('.qi-qty').val()) || 1;
                let price = parseFloat(currentQiEditRow.find('.qi-rate').val()) || 0;
                let discount = parseFloat(currentQiEditRow.find('.qi-discount').val()) || 0;
                let taxRate = parseFloat(currentQiEditRow.find('.qi-tax-rate').val()) || 0;

                let retailPrice = parseFloat(currentQiEditRow.attr('data-retail-price')) || price;
                let wsPrice = parseFloat(currentQiEditRow.attr('data-wholesale-price')) || 0;

                let costDefault = parseFloat(currentQiEditRow.attr('data-cost-default')) || 0;
                let costLowest = parseFloat(currentQiEditRow.attr('data-cost-lowest')) || costDefault;
                let costAvg = parseFloat(currentQiEditRow.attr('data-cost-avg')) || costDefault;
                let costHighest = parseFloat(currentQiEditRow.attr('data-cost-highest')) || costDefault;

                $('#qi_edit_product_title').text(name + ' (' + code + ')');
                $('#qi_modal_qty').val(qty);
                $('#qi_modal_price').val(price.toFixed(2));
                $('#qi_modal_discount').val(discount.toFixed(2));

                // Populate Price Options
                let $priceOpt = $('#qi_modal_price_option');
                $priceOpt.empty();
                $priceOpt.append(`<option value="${retailPrice.toFixed(2)}" ${Math.abs(price - retailPrice) < 0.01 ? 'selected' : ''}>Retail Price: ${formatCurrency(retailPrice)}</option>`);
                if (wsPrice > 0) {
                    $priceOpt.append(`<option value="${wsPrice.toFixed(2)}" ${Math.abs(price - wsPrice) < 0.01 ? 'selected' : ''}>Wholesale Price: ${formatCurrency(wsPrice)}</option>`);
                }

                // Populate Tax Rate
                $('#qi_modal_tax_rate').val(taxRate);

                // Populate Product Unit
                let unitsNameStr = currentQiEditRow.attr('data-units-name') || '';
                let unitsOpStr = currentQiEditRow.attr('data-units-operator') || '';
                let unitsValStr = currentQiEditRow.attr('data-units-operation-value') || '';
                let currentUnit = currentQiEditRow.find('.qi-sale-unit-id').val();

                let $unitSelect = $('#qi_modal_unit');
                $unitSelect.empty();

                if (type === 'standard' && unitsNameStr) {
                    let uNames = unitsNameStr.split(',').filter(Boolean);
                    let uOps = unitsOpStr.split(',').filter(Boolean);
                    let uVals = unitsValStr.split(',').filter(Boolean);

                    uNames.forEach((uName, idx) => {
                        let op = uOps[idx] || '*';
                        let opVal = uVals[idx] || 1;
                        let isSelected = (currentUnit === uName || idx === 0) ? 'selected' : '';
                        $unitSelect.append(`<option value="${uName}" data-operator="${op}" data-operation-value="${opVal}" ${isSelected}>${uName}</option>`);
                    });
                    $('#qi_edit_unit_group').show();
                } else {
                    $('#qi_edit_unit_group').hide();
                }

                // Populate IMEI section
                let imeiStr = currentQiEditRow.find('.qi-imei-number').val() || '';
                let $imeiTbody = $('#qi_imei_table tbody');
                $imeiTbody.empty();
                if (imeiStr.trim().length > 0) {
                    let imeiArr = imeiStr.split(',').filter(Boolean);
                    imeiArr.forEach((imeiVal) => {
                        $imeiTbody.append(`
                            <tr>
                                <td class="py-1">
                                    <input type="text" class="form-control form-control-sm qi-modal-imei-val" value="${imeiVal}" readonly>
                                </td>
                                <td class="py-1 text-right" style="width: 40px;">
                                    <button type="button" class="btn btn-sm btn-danger qi-del-modal-imei py-0 px-2">X</button>
                                </td>
                            </tr>
                        `);
                    });
                    $('#qi_imei_section').show();
                } else {
                    $('#qi_imei_section').hide();
                }

                // Cost History
                $('#qi_modal_product_cost').text(formatCurrency(costDefault));
                $('#qi_modal_cost_lowest').text(formatCurrency(costLowest));
                $('#qi_modal_cost_avg').text(formatCurrency(costAvg));
                $('#qi_modal_cost_highest').text(formatCurrency(costHighest));

                $('#qiEditModal').modal('show');
            });

            // Delete IMEI row from modal
            $(document).on('click', '.qi-del-modal-imei', function() {
                $(this).closest('tr').remove();
                let remainingImeis = [];
                $('#qi_imei_table .qi-modal-imei-val').each(function() {
                    remainingImeis.push($(this).val());
                });
                $('#qi_modal_qty').val(remainingImeis.length || 1);
            });

            // Update row from Product Edit Modal
            $('#qi_modal_update_btn').on('click', function() {
                if (!currentQiEditRow) return;

                let newQty = parseFloat($('#qi_modal_qty').val()) || 1;
                let newPrice = parseFloat($('#qi_modal_price').val()) || 0;
                let newDiscount = parseFloat($('#qi_modal_discount').val()) || 0;
                let newTaxRate = parseFloat($('#qi_modal_tax_rate').val()) || 0;
                let newUnit = $('#qi_modal_unit').val() || '';

                let remainingImeis = [];
                $('#qi_imei_table .qi-modal-imei-val').each(function() {
                    remainingImeis.push($(this).val());
                });

                currentQiEditRow.find('.qi-qty').val(newQty);
                currentQiEditRow.find('.qi-rate').val(newPrice.toFixed(2));
                currentQiEditRow.find('.qi-discount').val(newDiscount.toFixed(2));
                currentQiEditRow.find('.qi-tax-rate').val(newTaxRate);
                if (newUnit) {
                    currentQiEditRow.find('.qi-sale-unit-id').val(newUnit);
                    currentQiEditRow.find('.qi-unit-display').text(newUnit);
                }
                if (remainingImeis.length) {
                    currentQiEditRow.find('.qi-imei-number').val(remainingImeis.join(','));
                }

                $('#qiEditModal').modal('hide');
                calculateQiTotals();
            });

            // Remove Row
            $(document).on('click', '.qi-remove-row', function() {
                $(this).closest('tr').remove();
                reindexQiRows();
                calculateQiTotals();
            });

            function reindexQiRows() {
                let idx = 1;
                $('#qi-order-table tbody tr').each(function() {
                    $(this).find('.qi-row-num').text(idx++);
                });
            }

            // Live calculate on qty, rate, and discount change
            $(document).on('input change', '.qi-qty, .qi-rate, .qi-discount', function() {
                calculateQiTotals();
            });

            function calculateQiTotals() {
                let grandTotal = 0;
                let totalQty = 0;
                let itemCount = 0;
                let totalDiscount = 0;
                let totalTax = 0;

                $('#qi-order-table tbody tr').each(function() {
                    let pid = $(this).find('.qi-product-id').val();
                    let qty = parseFloat($(this).find('.qi-qty').val()) || 0;
                    let rate = parseFloat($(this).find('.qi-rate').val()) || 0;
                    let discount = parseFloat($(this).find('.qi-discount').val()) || 0;
                    let taxRate = parseFloat($(this).find('.qi-tax-rate').val()) || 0;

                    let netPrice = Math.max(0, rate - discount);
                    let itemTax = netPrice * (taxRate / 100) * qty;
                    let subtotal = (netPrice * qty) + itemTax;

                    $(this).find('.qi-tax').val(itemTax.toFixed(2));
                    $(this).find('.qi-subtotal').val(subtotal.toFixed(2));
                    $(this).find('.qi-line-total').val(subtotal.toFixed(2));
                    $(this).find('.qi-net-unit-price').val(netPrice.toFixed(2));

                    if (pid && qty > 0) {
                        grandTotal += subtotal;
                        totalQty += qty;
                        totalDiscount += (discount * qty);
                        totalTax += itemTax;
                        itemCount++;
                    }
                });

                $('#qi-total-display').text(grandTotal.toFixed(2));
                $('#qi-qty-display').text(totalQty);
                $('#qi-item-display').text(itemCount);

                $('#qi_grand_total').val(grandTotal.toFixed(2));
                $('#qi_total_price').val(grandTotal.toFixed(2));
                $('#qi_total_qty').val(totalQty);
                $('#qi_total_discount').val(totalDiscount.toFixed(2));
                $('#qi_total_tax').val(totalTax.toFixed(2));
                $('#qi_item').val(itemCount);
                $('#qi_paid_amount').val(grandTotal.toFixed(2));
                $('#qi_paying_amount').val(grandTotal.toFixed(2));
            }

            $('#qi_paid_amount').on('input', function() {
                $('#qi_paying_amount').val($(this).val());
            });

            // Focus search input and load customer details when modal opens
            $('#quickInvoiceModal').on('show.bs.modal shown.bs.modal', function() {
                clearQiSearchResults();
                updateQiCustomerAddress();
                setTimeout(function() {
                    $('#qi_product_search_input').focus();
                }, 100);
            });

            // Submit Quick Invoice and instantly Print in the SAME tab
            $('#qi-submit-btn').on('click', function() {
                let form = $('#quick-invoice-form');

                let validRows = 0;
                $('#qi-order-table tbody tr').each(function() {
                    let pid = $(this).find('.qi-product-id').val();
                    let qty = parseFloat($(this).find('.qi-qty').val()) || 0;
                    if (pid && qty > 0) {
                        validRows++;
                    }
                });

                if (validRows === 0) {
                    alert("Please search and select at least one product with quantity > 0.");
                    $searchInput.focus();
                    return;
                }

                let submitBtn = $(this);
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Processing...');

                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        submitBtn.prop('disabled', false).html('<i class="ti ti-printer mr-1"></i> Save & Print Invoice');

                        let sale_id = null;
                        if (typeof response === 'object') {
                            sale_id = response.sale_id || response.id || response;
                        } else {
                            sale_id = response;
                        }

                        $('#quickInvoiceModal').modal('hide');

                        // Reset form for next invoice
                        $('#qi-order-table tbody').empty();
                        calculateQiTotals();
                        $searchInput.val('');

                        if (sale_id && !isNaN(sale_id)) {
                            // Print invoice in the SAME tab using hidden iframe
                            let printUrl = "{{ url('sales/gen_invoice') }}/" + sale_id + "?is_print=true";
                            let $iframe = $('#qi-print-iframe');
                            if (!$iframe.length) {
                                $iframe = $('<iframe id="qi-print-iframe" name="qi-print-iframe" style="position:fixed; top:-9999px; left:-9999px; width:1px; height:1px; border:none; visibility:hidden;"></iframe>').appendTo('body');
                            }
                            $iframe.off('load').on('load', function() {
                                setTimeout(function() {
                                    try {
                                        let iframeWin = $iframe[0].contentWindow || $iframe[0];
                                        iframeWin.focus();
                                        iframeWin.print();
                                    } catch (e) {
                                        console.error('Invoice print error:', e);
                                    }
                                }, 300);
                            });
                            $iframe.attr('src', printUrl);
                        } else {
                            alert("Invoice created successfully!");
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('<i class="ti ti-printer mr-1"></i> Save & Print Invoice');
                        let errorMsg = 'Error creating invoice.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        } else if (xhr.responseText) {
                            errorMsg = xhr.responseText.substring(0, 150);
                        }
                        alert(errorMsg);
                        console.error('Quick Invoice Error:', xhr);
                    }
                });
            });
        });

    </script>

    <!-- Quick Invoice Modal -->
    <div id="quickInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="quickInvoiceLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog modal-xl" style="max-width: 92vw; margin: 1rem auto;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2 px-3">
                    <h5 id="quickInvoiceLabel" class="modal-title font-weight-bold" style="color: #7c5cc4;"><i class="ti ti-file-invoice mr-1"></i> {{__('Quick Invoice')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
                </div>
                <div class="modal-body p-3">
                    <form id="quick-invoice-form" action="{{ route('sales.store') }}" method="POST">
                        @csrf
                        @php
                            $lims_warehouse_list = App\Models\Warehouse::where('is_active', true)->get();
                            $lims_biller_list = App\Models\Biller::where('is_active', true)->get();
                            $lims_customer_list = App\Models\Customer::where('is_active', true)->get();
                            $lims_tax_list = App\Models\Tax::where('is_active', true)->get();
                            $lims_pos_setting_data = App\Models\PosSetting::latest()->first();
                            $default_customer_id = $lims_pos_setting_data->customer_id ?? ($lims_customer_list[0]->id ?? 1);
                            $default_warehouse_id = $lims_pos_setting_data->warehouse_id ?? ($lims_warehouse_list[0]->id ?? 1);
                            $default_biller_id = $lims_pos_setting_data->biller_id ?? ($lims_biller_list[0]->id ?? 1);

                            $tax_name_all = ['No Tax'];
                            $tax_rate_all = [0];
                            foreach($lims_tax_list as $tax) {
                                $tax_name_all[] = $tax->name;
                                $tax_rate_all[] = $tax->rate;
                            }
                        @endphp

                        <div class="row mb-2">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold">{{__('Customer')}} *</label>
                                    <div class="input-group">
                                        <select required name="customer_id" id="qi_customer_id" class="selectpicker form-control form-control-sm" data-live-search="true" title="Select customer...">
                                            @foreach($lims_customer_list as $customer)
                                                <option value="{{$customer->id}}" 
                                                        data-name="{{$customer->name}}"
                                                        data-company="{{$customer->company_name}}"
                                                        data-phone="{{$customer->phone_number}}"
                                                        data-address="{{$customer->address}}"
                                                        data-city="{{$customer->city}}"
                                                        data-state="{{$customer->state}}"
                                                        data-postal="{{$customer->postal_code}}"
                                                        data-country="{{$customer->country}}"
                                                        @if($customer->id == $default_customer_id) selected @endif>
                                                    {{$customer->name}} @if($customer->phone_number)({{$customer->phone_number}})@endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#addCustomer" title="Add New Customer"><i class="ti ti-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold">{{__('Warehouse')}} *</label>
                                    <select required name="warehouse_id" id="qi_warehouse_id" class="selectpicker form-control form-control-sm" data-live-search="true" title="Select warehouse...">
                                        @foreach($lims_warehouse_list as $warehouse)
                                            <option value="{{$warehouse->id}}" @if($warehouse->id == $default_warehouse_id) selected @endif>{{$warehouse->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold">{{__('Biller')}} *</label>
                                    <select required name="biller_id" id="qi_biller_id" class="selectpicker form-control form-control-sm" data-live-search="true" title="Select biller...">
                                        @foreach($lims_biller_list as $biller)
                                            <option value="{{$biller->id}}" @if($biller->id == $default_biller_id) selected @endif>{{$biller->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Bill To & Ship To Details Box (Modern & Clean) -->
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="card border mb-2 shadow-sm" style="border-radius: 6px;">
                                    <div class="card-header py-1 px-3 bg-light font-weight-bold d-flex justify-content-between align-items-center" style="font-size: 12px; color: #7c5cc4;">
                                        <span><i class="ti ti-user mr-1"></i> {{__('Bill To')}}</span>
                                        <span class="badge badge-secondary px-2 py-0" style="font-size: 10px;">Billing Address</span>
                                    </div>
                                    <div class="card-body p-2" id="qi_bill_to_content" style="font-size: 12px; min-height: 48px; line-height: 1.4;">
                                        <span class="text-muted">Loading customer details...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border mb-2 shadow-sm" style="border-radius: 6px;">
                                    <div class="card-header py-1 px-3 bg-light font-weight-bold d-flex justify-content-between align-items-center" style="font-size: 12px; color: #7c5cc4;">
                                        <span><i class="ti ti-truck mr-1"></i> {{__('Ship To')}}</span>
                                        <span class="badge badge-secondary px-2 py-0" style="font-size: 10px;">Shipping Address</span>
                                    </div>
                                    <div class="card-body p-2" id="qi_ship_to_content" style="font-size: 12px; min-height: 48px; line-height: 1.4;">
                                        <span class="text-muted">Loading customer details...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Search Bar (Like Sale/Purchase Create) -->
                        <div class="search-box form-group mb-3 position-relative">
                            <label class="font-weight-bold">{{__('Select Product')}} *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background-color: #7c5cc4; color: #fff; border-color: #7c5cc4;"><i class="ti ti-barcode"></i></span>
                                </div>
                                <input type="text" id="qi_product_search_input" placeholder="Please type product name or code and select..." class="form-control" autocomplete="off" style="border: 1px solid #7c5cc4;" />
                            </div>
                            <div id="qi_product_results_container" class="dropdown-menu w-100 shadow-lg border" style="display:none; max-height: 280px; overflow-y: auto; z-index: 1060; margin-top: 2px;"></div>
                            <div id="qi_no_results_message" style="display:none; background-color: #f8fafc; color: #64748b; padding: 6px 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 4px; margin-top: 4px;">{{__('No in-stock products found matching your search.')}}</div>
                        </div>

                        <!-- Hidden required fields -->
                        <input type="hidden" name="exchange_rate" value="1">
                        <input type="hidden" name="currency_id" value="1">
                        <input type="hidden" name="sale_status" value="1">
                        <input type="hidden" name="coupon_active" value="0">
                        <input type="hidden" name="redeem_point" value="0">
                        <input type="hidden" name="draft" value="0">
                        <input type="hidden" name="item" id="qi_item" value="0">
                        <input type="hidden" name="total_qty" id="qi_total_qty" value="0">
                        <input type="hidden" name="total_discount" id="qi_total_discount" value="0">
                        <input type="hidden" name="total_tax" id="qi_total_tax" value="0">
                        <input type="hidden" name="total_price" id="qi_total_price" value="0">
                        <input type="hidden" name="grand_total" id="qi_grand_total" value="0">
                        <input type="hidden" name="order_tax_rate" value="0">
                        <input type="hidden" name="order_tax" value="0">
                        <input type="hidden" name="order_discount" value="0">
                        <input type="hidden" name="shipping_cost" value="0">
                        <input type="hidden" name="payment_status" value="4">

                        <div class="table-responsive border rounded qi-product-table-wrapper" style="max-height: 240px; min-height: 120px; overflow-y: auto; overflow-x: auto; background: #fff;">
                            <table class="table table-bordered table-sm mb-0" id="qi-order-table" style="font-size: 0.9rem; width: 100%;">
                                <thead style="position: sticky; top: 0; background-color: #f8fafc !important; z-index: 10;">
                                    <tr>
                                        <th style="width: 35px;" class="text-center">#</th>
                                        <th style="width: 115px;">{{__('Item Code')}}</th>
                                        <th style="min-width: 200px;">{{__('Description')}} *</th>
                                        <th style="width: 95px;">{{__('Quantity')}} *</th>
                                        <th style="width: 70px;" class="text-center">{{__('UM')}}</th>
                                        <th style="width: 110px;">{{__('Unit Price')}} *</th>
                                        <th style="width: 90px;">{{__('Discount')}}</th>
                                        <th style="width: 80px;">{{__('Tax')}}</th>
                                        <th style="width: 115px;">{{__('Subtotal')}}</th>
                                        <th style="width: 75px;" class="text-center">{{__('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Rows added when product is selected from search -->
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3 pt-2 border-top">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold">{{__('Paying Method')}} *</label>
                                    <select name="paid_by_id[]" class="form-control form-control-sm">
                                        <option value="1">Cash</option>
                                        <option value="3">Credit Card</option>
                                        <option value="4">Cheque</option>
                                        <option value="6">Deposit</option>
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">{{__('Sale Note')}} / Memo</label>
                                    <textarea name="sale_note" rows="2" class="form-control form-control-sm" placeholder="Optional sale notes / memo..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm mb-2">
                                    <tr>
                                        <td class="text-right py-1 align-middle font-weight-bold">{{__('Total Items')}} / Pieces:</td>
                                        <td class="text-right py-1" style="width: 150px;"><span id="qi-item-display" class="badge badge-secondary px-2 py-1">0</span> (<span id="qi-qty-display">0</span> Qty)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-right py-1 align-middle font-weight-bold">{{__('Grand Total')}}:</td>
                                        <td class="text-right py-1"><span id="qi-total-display" style="font-size: 1.35rem; font-weight: bold; color: #7c5cc4;">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-right py-1 align-middle font-weight-bold">{{__('Paid Amount')}} *:</td>
                                        <td class="py-1">
                                            <input type="number" name="paid_amount[]" class="form-control form-control-sm text-right font-weight-bold" value="0" step="any" id="qi_paid_amount" required>
                                            <input type="hidden" name="paying_amount[]" id="qi_paying_amount" value="0">
                                            <input type="hidden" name="payment_note" value="">
                                            <input type="hidden" name="cheque_no" value="">
                                            <input type="hidden" name="card_number" value="">
                                            <input type="hidden" name="card_holder_name" value="">
                                            <input type="hidden" name="card_type" value="">
                                            <input type="hidden" name="gift_card_id" value="">
                                        </td>
                                    </tr>
                                </table>
                                <div class="text-right">
                                    <button type="button" class="btn btn-success px-4 font-weight-bold shadow-sm" style="background-color: #7c5cc4; border-color: #7c5cc4;" id="qi-submit-btn">
                                        <i class="ti ti-printer mr-1"></i> {{__('Save & Print Invoice')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Invoice Product Edit Modal (Exact Match to Sale Edit Modal) -->
    <div id="qiEditModal" tabindex="-1" role="dialog" aria-labelledby="qiEditModalLabel" aria-hidden="true" class="modal fade text-left" style="z-index: 1065;">
        <div role="document" class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light py-2 px-3">
                    <h5 id="qi_modal_product_name_header" class="modal-title font-weight-bold text-dark">
                        <i class="ti ti-edit mr-1 text-primary"></i> <span id="qi_edit_product_title">{{__('Edit Product')}}</span>
                    </h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
                </div>
                <div class="modal-body p-3">
                    <form id="qi-edit-item-form">
                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold" style="font-size: 13px;">{{__('Quantity')}} *</label>
                                <input type="number" id="qi_modal_qty" class="form-control font-weight-bold" step="any" min="0.01">
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold" style="font-size: 13px;">{{__('Unit Discount')}}</label>
                                <input type="number" id="qi_modal_discount" class="form-control font-weight-bold" step="any" min="0" value="0">
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold" style="font-size: 13px;">{{__('Price Option')}}</label>
                                <select id="qi_modal_price_option" class="form-control">
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold" style="font-size: 13px;">{{__('Unit Price')}} *</label>
                                <input type="number" id="qi_modal_price" class="form-control font-weight-bold" step="any">
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold" style="font-size: 13px;">{{__('Tax Rate')}}</label>
                                <select id="qi_modal_tax_rate" class="form-control">
                                    @foreach($tax_name_all as $key => $name)
                                        <option value="{{$tax_rate_all[$key]}}" data-name="{{$name}}">{{$name}} ({{$tax_rate_all[$key]}}%)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-3" id="qi_edit_unit_group">
                                <label class="font-weight-bold" style="font-size: 13px;">{{__('Product Unit')}}</label>
                                <select id="qi_modal_unit" class="form-control">
                                </select>
                            </div>
                            <div class="col-md-12 form-group mb-2" id="qi_imei_section" style="display:none;">
                                <label class="font-weight-bold" style="font-size: 13px;">{{__('IMEI or Serial Numbers')}}</label>
                                <div class="table-responsive border rounded p-2 bg-light" style="max-height: 120px; overflow-y:auto;">
                                    <table id="qi_imei_table" class="table table-sm table-borderless mb-0">
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Price History (Lowest, Average, Highest Cost) -->
                        <div class="mt-2 p-3 border rounded shadow-sm" style="background:#f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong style="font-size:13px; color:#334155;">
                                    <i class="ti ti-coins text-primary mr-1"></i> {{__('Purchase Price History')}}
                                </strong>
                                <span class="badge badge-light border text-muted" style="font-size:12px;">
                                    Default Cost: <span id="qi_modal_product_cost" class="font-weight-bold text-dark">0.00</span>
                                </span>
                            </div>
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted d-block font-weight-bold mb-1" style="font-size:12px;">Lowest Cost</small>
                                    <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size:14px;" id="qi_modal_cost_lowest">0.00</span>
                                </div>
                                <div class="col-4" style="border-left: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1;">
                                    <small class="text-muted d-block font-weight-bold mb-1" style="font-size:12px;">Average Cost</small>
                                    <span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size:14px;" id="qi_modal_cost_avg">0.00</span>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block font-weight-bold mb-1" style="font-size:12px;">Highest Cost</small>
                                    <span class="badge badge-danger px-3 py-1 font-weight-bold" style="font-size:14px;" id="qi_modal_cost_highest">0.00</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{__('Close')}}</button>
                    <button type="button" id="qi_modal_update_btn" class="btn btn-primary btn-sm px-4 font-weight-bold" style="background-color: #7c5cc4; border-color: #7c5cc4;">
                        <i class="ti ti-check mr-1"></i> {{__('Update')}}
                    </button>
                </div>
            </div>
        </div>
    </div>

@endpush
