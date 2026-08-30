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

class DeliveryReportController extends Controller
{
    public function index()
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

            return view('backend.delivery_management.delivery_report.index', compact('lims_delivery_man_list', 'lims_route_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
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
