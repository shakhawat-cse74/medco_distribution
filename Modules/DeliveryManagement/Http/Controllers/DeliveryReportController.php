<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DeliveryManagement\Models\DeliveryMan;
use Modules\DeliveryManagement\Models\FieldOrder;
use Modules\DeliveryManagement\Models\FieldPayment;
use Modules\DeliveryManagement\Models\DeliveryManCommission;
use Modules\DeliveryManagement\Models\CashDeposit;
use Modules\DeliveryManagement\Models\CustomerVisit;
use Modules\DeliveryManagement\Models\DeliveryManDelivery;
use Modules\DeliveryManagement\Models\DeliveryManRoute;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\DeliveryManagement\Traits\ChecksDeliveryManRole;

class DeliveryReportController extends Controller
{
    use ChecksDeliveryManRole;

    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-reports-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
            $lims_route_list = DeliveryManRoute::where('is_active', true)->get();

            $period = $request->input('period', 'today');
            $startDate = $request->input('start_date', date('Y-m-d'));
            $endDate = $request->input('end_date', date('Y-m-d'));
            $selectedDeliveryManId = $request->input('delivery_man_id');

            // If delivery man user, force filter to their own data
            $isDeliveryMan = $this->isDeliveryManUser();
            if ($isDeliveryMan) {
                $authDeliveryMan = $this->getAuthDeliveryMan();
                $selectedDeliveryManId = $authDeliveryMan ? $authDeliveryMan->id : null;
            }

            switch ($period) {
                case 'week':
                    $startDate = date('Y-m-d', strtotime('monday this week'));
                    $endDate = date('Y-m-d', strtotime('sunday this week'));
                    break;
                case 'month':
                    $startDate = date('Y-m-01');
                    $endDate = date('Y-m-t');
                    break;
                case 'custom':
                    if (!$request->filled('start_date') || !$request->filled('end_date')) {
                        $startDate = date('Y-m-d');
                        $endDate = date('Y-m-d');
                    }
                    break;
                default:
                    $startDate = date('Y-m-d');
                    $endDate = date('Y-m-d');
            }

            $startDateTime = $startDate . ' 00:00:00';
            $endDateTime = $endDate . ' 23:59:59';

        $activeDeliveryManIds = DeliveryMan::where('is_active', true)->when($selectedDeliveryManId, function ($query) use ($selectedDeliveryManId) {
            $query->where('id', $selectedDeliveryManId);
        })->pluck('id')->toArray();

        $baseQuery = FieldOrder::query()
            ->whereIn('delivery_man_id', $activeDeliveryManIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime]);

            if ($selectedDeliveryManId) {
                $baseQuery->where('delivery_man_id', $selectedDeliveryManId);
            }

        $stats = [
                'total_delivery_men' => DeliveryMan::where('is_active', true)->when($selectedDeliveryManId, function ($query) use ($selectedDeliveryManId) {
                    $query->where('id', $selectedDeliveryManId);
                })->count(),
                'total_orders' => (clone $baseQuery)->count(),
                'total_collection' => (float) ((clone $baseQuery)->sum('paid_amount') ?? 0),
                'pending_deliveries' => DeliveryManDelivery::where('status', 'assigned')->when($selectedDeliveryManId, function ($query) use ($selectedDeliveryManId) {
                    $query->where('delivery_man_id', $selectedDeliveryManId);
                })->count(),
                'completed_orders' => (clone $baseQuery)->where('status', 'completed')->count(),
                'pending_orders' => (clone $baseQuery)->where('status', 'pending')->count(),
                'total_due' => (float) ((clone $baseQuery)->sum('due_amount') ?? 0),
                'cancelled_orders' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            ];

            $deliveryManStats = [];
            $deliveryMenQuery = DeliveryMan::where('is_active', true);
            if ($selectedDeliveryManId) {
                $deliveryMenQuery->where('id', $selectedDeliveryManId);
            }

            $allOrders = FieldOrder::query()
                ->whereIn('delivery_man_id', $activeDeliveryManIds)
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->get()
                ->groupBy('delivery_man_id');

            foreach ($deliveryMenQuery->get() as $deliveryMan) {
                $orders = $allOrders->get($deliveryMan->id, collect());
                $deliveryManStats[] = [
                    'delivery_man' => $deliveryMan,
                    'total_orders' => $orders->count(),
                    'completed_orders' => $orders->where('status', 'completed')->count(),
                    'pending_orders' => $orders->where('status', 'pending')->count(),
                    'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
                    'total_collection' => (float) ($orders->sum('paid_amount') ?? 0),
                    'total_due' => (float) ($orders->sum('due_amount') ?? 0),
                ];
            }

            $chartData = $this->getChartData($period, $startDate, $endDate, $selectedDeliveryManId, $activeDeliveryManIds);

            return view('backend.delivery_management.delivery_report.index', compact(
                'lims_delivery_man_list', 'lims_route_list', 'all_permission',
                'period', 'startDate', 'endDate', 'stats', 'deliveryManStats', 'chartData', 'selectedDeliveryManId', 'isDeliveryMan'
            ));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function dashboardData(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-reports-index')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $period = $request->input('period', 'today');
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $selectedDeliveryManId = $request->input('delivery_man_id');

        // If delivery man user, force filter to their own data
        $isDeliveryMan = $this->isDeliveryManUser();
        if ($isDeliveryMan) {
            $authDeliveryMan = $this->getAuthDeliveryMan();
            $selectedDeliveryManId = $authDeliveryMan ? $authDeliveryMan->id : null;
        }

        switch ($period) {
            case 'week':
                $startDate = date('Y-m-d', strtotime('monday this week'));
                $endDate = date('Y-m-d', strtotime('sunday this week'));
                break;
            case 'month':
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                break;
            case 'custom':
                if (!$request->filled('start_date') || !$request->filled('end_date')) {
                    $startDate = date('Y-m-d');
                    $endDate = date('Y-m-d');
                }
                break;
            default:
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d');
        }

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $activeDeliveryManIds = DeliveryMan::where('is_active', true)->when($selectedDeliveryManId, function ($query) use ($selectedDeliveryManId) {
            $query->where('id', $selectedDeliveryManId);
        })->pluck('id')->toArray();

        $baseQuery = FieldOrder::query()
            ->whereIn('delivery_man_id', $activeDeliveryManIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime]);

        if ($selectedDeliveryManId) {
            $baseQuery->where('delivery_man_id', $selectedDeliveryManId);
        }

        $stats = [
            'total_orders' => (clone $baseQuery)->count(),
            'total_collection' => (float) ((clone $baseQuery)->sum('paid_amount') ?? 0),
            'completed_orders' => (clone $baseQuery)->where('status', 'completed')->count(),
            'pending_orders' => (clone $baseQuery)->where('status', 'pending')->count(),
            'total_due' => (float) ((clone $baseQuery)->sum('due_amount') ?? 0),
            'cancelled_orders' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'pending_deliveries' => DeliveryManDelivery::where('status', 'assigned')->when($selectedDeliveryManId, function ($query) use ($selectedDeliveryManId) {
                $query->where('delivery_man_id', $selectedDeliveryManId);
            })->count(),
            'total_delivery_men' => DeliveryMan::where('is_active', true)->when($selectedDeliveryManId, function ($query) use ($selectedDeliveryManId) {
                $query->where('id', $selectedDeliveryManId);
            })->count(),
        ];

        $deliveryManStats = [];
        $deliveryMenQuery = DeliveryMan::where('is_active', true);
        if ($selectedDeliveryManId) {
            $deliveryMenQuery->where('id', $selectedDeliveryManId);
        }

        $allOrders = FieldOrder::query()
            ->whereIn('delivery_man_id', $activeDeliveryManIds)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->get()
            ->groupBy('delivery_man_id');

        foreach ($deliveryMenQuery->get() as $deliveryMan) {
            $orders = $allOrders->get($deliveryMan->id, collect());
            $deliveryManStats[] = [
                'delivery_man' => $deliveryMan,
                'total_orders' => $orders->count(),
                'completed_orders' => $orders->where('status', 'completed')->count(),
                'pending_orders' => $orders->where('status', 'pending')->count(),
                'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
                'total_collection' => (float) ($orders->sum('paid_amount') ?? 0),
                'total_due' => (float) ($orders->sum('due_amount') ?? 0),
            ];
        }

        $chartData = $this->getChartData($period, $startDate, $endDate, $selectedDeliveryManId, $activeDeliveryManIds);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'chartData' => $chartData,
            'deliveryManStats' => $deliveryManStats,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    private function getChartData($period, $startDate, $endDate, $deliveryManId = null, $activeDeliveryManIds = [])
    {
        $labels = [];
        $ordersData = [];
        $collectionData = [];
        $dueData = [];

        $query = FieldOrder::query()
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('delivery_man_id', $activeDeliveryManIds);
        if ($deliveryManId) {
            $query->where('delivery_man_id', $deliveryManId);
        }

        if ($period === 'today') {
            for ($i = 0; $i < 24; $i++) {
                $labels[] = date('h A', mktime($i, 0, 0));
                $ordersData[] = (clone $query)->whereDate('created_at', date('Y-m-d'))
                    ->whereRaw('HOUR(created_at) = ?', [$i])
                    ->count();
                $collectionData[] = (float) ((clone $query)->whereDate('created_at', date('Y-m-d'))
                    ->whereRaw('HOUR(created_at) = ?', [$i])
                    ->sum('paid_amount') ?? 0);
                $dueData[] = (float) ((clone $query)->whereDate('created_at', date('Y-m-d'))
                    ->whereRaw('HOUR(created_at) = ?', [$i])
                    ->sum('due_amount') ?? 0);
            }
        } elseif ($period === 'week') {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $end->modify('+1 day');
            $interval = new \DateInterval('P1D');
            $daterange = new \DatePeriod($start, $interval, $end);

            foreach ($daterange as $date) {
                $labels[] = $date->format('D');
                $dateStr = $date->format('Y-m-d');
                $ordersData[] = (clone $query)->whereDate('created_at', $dateStr)->count();
                $collectionData[] = (float) ((clone $query)->whereDate('created_at', $dateStr)->sum('paid_amount') ?? 0);
                $dueData[] = (float) ((clone $query)->whereDate('created_at', $dateStr)->sum('due_amount') ?? 0);
            }
        } elseif ($period === 'month') {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $end->modify('+1 day');
            $interval = new \DateInterval('P1D');
            $daterange = new \DatePeriod($start, $interval, $end);

            foreach ($daterange as $date) {
                $labels[] = $date->format('d M');
                $dateStr = $date->format('Y-m-d');
                $ordersData[] = (clone $query)->whereDate('created_at', $dateStr)->count();
                $collectionData[] = (float) ((clone $query)->whereDate('created_at', $dateStr)->sum('paid_amount') ?? 0);
                $dueData[] = (float) ((clone $query)->whereDate('created_at', $dateStr)->sum('due_amount') ?? 0);
            }
        } else {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $end->modify('+1 day');
            $interval = new \DateInterval('P1D');
            $daterange = new \DatePeriod($start, $interval, $end);

            foreach ($daterange as $date) {
                $labels[] = $date->format('d M');
                $dateStr = $date->format('Y-m-d');
                $ordersData[] = (clone $query)->whereDate('created_at', $dateStr)->count();
                $collectionData[] = (float) ((clone $query)->whereDate('created_at', $dateStr)->sum('paid_amount') ?? 0);
                $dueData[] = (float) ((clone $query)->whereDate('created_at', $dateStr)->sum('due_amount') ?? 0);
            }
        }

        return [
            'labels' => $labels,
            'orders' => $ordersData,
            'collection' => $collectionData,
            'due' => $dueData,
        ];
    }

    public function deliveryManWiseOrder()
    {
        $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
        $report = [];

        foreach ($lims_delivery_man_list as $deliveryMan) {
            $report[] = [
                'delivery_man' => $deliveryMan,
                'total_orders' => $deliveryMan->fieldOrders()->count(),
                'completed_orders' => $deliveryMan->fieldOrders()->where('status', 'completed')->count(),
                'pending_orders' => $deliveryMan->fieldOrders()->where('status', 'pending')->count(),
                'cancelled_orders' => $deliveryMan->fieldOrders()->where('status', 'cancelled')->count(),
            ];
        }

        return view('backend.delivery_management.delivery_report.delivery_man_wise_order', compact('report'));
    }

    public function deliveryManWiseCollection()
    {
        $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
        $report = [];

        foreach ($lims_delivery_man_list as $deliveryMan) {
            $total_collection = $deliveryMan->fieldOrders()->sum('paid_amount');
            $total_due = $deliveryMan->fieldOrders()->sum('due_amount');
            $report[] = [
                'delivery_man' => $deliveryMan,
                'total_collection' => $total_collection,
                'total_due' => $total_due,
            ];
        }

        return view('backend.delivery_management.delivery_report.delivery_man_wise_collection', compact('report'));
    }

    public function deliveryManWiseDue()
    {
        $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
        $report = [];

        foreach ($lims_delivery_man_list as $deliveryMan) {
            $total_due = $deliveryMan->fieldOrders()->sum('due_amount');
            $report[] = [
                'delivery_man' => $deliveryMan,
                'total_due' => $total_due,
            ];
        }

        return view('backend.delivery_management.delivery_report.delivery_man_wise_due', compact('report'));
    }

    public function areaWiseSales()
    {
        $areaSales = FieldOrder::select('delivery_city', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(grand_total) as total_sales'))
            ->groupBy('delivery_city')
            ->get();

        return view('backend.delivery_management.delivery_report.area_wise_sales', compact('areaSales'));
    }

    public function deliveryPerformance()
    {
        $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
        $report = [];

        foreach ($lims_delivery_man_list as $deliveryMan) {
            $total_deliveries = $deliveryMan->deliveries()->count();
            $completed_deliveries = $deliveryMan->deliveries()->where('status', 'completed')->count();
            $pending_deliveries = $deliveryMan->deliveries()->where('status', 'assigned')->count();
            $completion_rate = $total_deliveries > 0 ? round(($completed_deliveries / $total_deliveries) * 100, 2) : 0;

            $report[] = [
                'delivery_man' => $deliveryMan,
                'total_deliveries' => $total_deliveries,
                'completed_deliveries' => $completed_deliveries,
                'pending_deliveries' => $pending_deliveries,
                'completion_rate' => $completion_rate,
            ];
        }

        return view('backend.delivery_management.delivery_report.delivery_performance', compact('report'));
    }

    public function commissionReport()
    {
        $lims_commission_list = DeliveryManCommission::with(['deliveryMan', 'fieldOrder'])->get();
        return view('backend.delivery_management.delivery_report.commission_report', compact('lims_commission_list'));
    }

    public function commissionPayout()
    {
        $lims_commission_list = DeliveryManCommission::with(['deliveryMan'])
            ->where('status', 'pending')
            ->get();

        $grouped = $lims_commission_list->groupBy('delivery_man_id');
        return view('backend.delivery_management.delivery_report.commission_payout', compact('grouped'));
    }

    public function cashReconciliation()
    {
        $start_date = request('start_date', date('Y-m-d', strtotime('-30 days')));
        $end_date = request('end_date', date('Y-m-d'));

        $payments = FieldPayment::whereBetween('created_at', [$start_date, $end_date])
            ->with(['fieldOrder.deliveryMan'])
            ->get();

        $deposits = CashDeposit::whereBetween('created_at', [$start_date, $end_date])
            ->with('deliveryMan')
            ->get();

        return view('backend.delivery_management.delivery_report.cash_reconciliation', compact('payments', 'deposits', 'start_date', 'end_date'));
    }

    public function customerVisitReport()
    {
        $lims_visit_list = CustomerVisit::with(['deliveryMan', 'customer'])
            ->whereMonth('check_in_at', date('m'))
            ->get();

        return view('backend.delivery_management.delivery_report.customer_visit_report', compact('lims_visit_list'));
    }

    public function productWiseFieldSale()
    {
        $productSales = DB::table('field_order_products')
            ->join('field_orders', 'field_orders.id', '=', 'field_order_products.field_order_id')
            ->join('products', 'products.id', '=', 'field_order_products.product_id')
            ->select('products.name', DB::raw('SUM(field_order_products.qty) as total_qty'), DB::raw('SUM(field_order_products.sub_total) as total_amount'))
            ->groupBy('products.id', 'products.name')
            ->get();

        return view('backend.delivery_management.delivery_report.product_wise_field_sale', compact('productSales'));
    }

    public function deliveryManDashboard($id)
    {
        $lims_delivery_man_data = DeliveryMan::with(['fieldOrders', 'deliveries', 'commissions', 'cashDeposits', 'visits', 'schedules'])->findOrFail($id);

        $totalOrders = $lims_delivery_man_data->fieldOrders()->count();
        $completedOrders = $lims_delivery_man_data->fieldOrders()->where('status', 'completed')->count();
        $totalCollection = $lims_delivery_man_data->fieldOrders()->sum('paid_amount');
        $totalDue = $lims_delivery_man_data->fieldOrders()->sum('due_amount');
        $totalCommission = $lims_delivery_man_data->commissions()->sum('commission_amount');
        $totalDeposits = $lims_delivery_man_data->cashDeposits()->sum('amount');
        $totalVisits = $lims_delivery_man_data->visits()->count();
        $totalDeliveries = $lims_delivery_man_data->deliveries()->count();

        return view('backend.delivery_management.delivery_report.delivery_man_dashboard', compact(
            'lims_delivery_man_data',
            'totalOrders',
            'completedOrders',
            'totalCollection',
            'totalDue',
            'totalCommission',
            'totalDeposits',
            'totalVisits',
            'totalDeliveries'
        ));
    }
}
