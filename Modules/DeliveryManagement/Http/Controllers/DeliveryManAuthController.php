<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DeliveryManagement\Models\DeliveryMan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DeliveryManAuthController extends Controller
{
    public function showLogin()
    {
        return view('backend.delivery_management.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::guard('delivery_man')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $deliveryMan = Auth::guard('delivery_man')->user();

            if (!$deliveryMan->is_active) {
                Auth::guard('delivery_man')->logout();
                $request->session()->invalidate();
                return redirect()->back()->with('not_permitted', __('db.Sorry! Your account is inactive'));
            }

            return redirect()->route('delivery-man.dashboard');
        }

        return redirect()->back()->with('not_permitted', __('db.Invalid email or password'));
    }

    public function dashboard(Request $request)
    {
        $deliveryMan = Auth::guard('delivery_man')->user();

        $period = $request->input('period', 'today');
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));

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

        $baseQuery = $deliveryMan->fieldOrders()->whereBetween('created_at', [$startDateTime, $endDateTime]);

        $totalOrders = $baseQuery->count();
        $completedOrders = $baseQuery->where('status', 'completed')->count();
        $pendingOrders = $baseQuery->where('status', 'pending')->count();
        $cancelledOrders = $baseQuery->where('status', 'cancelled')->count();

        $todayOrders = $deliveryMan->fieldOrders()->whereDate('created_at', today())->count();
        $todayCollection = $deliveryMan->fieldOrders()->whereDate('created_at', today())->sum('paid_amount');
        $todayDue = $deliveryMan->fieldOrders()->whereDate('created_at', today())->sum('due_amount');

        $weekOrders = $deliveryMan->fieldOrders()->whereBetween('created_at', [now()->subDays(7), now()])->count();
        $weekCollection = $deliveryMan->fieldOrders()->whereBetween('created_at', [now()->subDays(7), now()])->sum('paid_amount');

        $monthOrders = $deliveryMan->fieldOrders()->whereMonth('created_at', date('m'))->count();
        $monthCollection = $deliveryMan->fieldOrders()->whereMonth('created_at', date('m'))->sum('paid_amount');

        $totalCollection = $deliveryMan->fieldOrders()->sum('paid_amount');
        $totalDue = $deliveryMan->fieldOrders()->sum('due_amount');

        $recentOrders = $deliveryMan->fieldOrders()->latest()->take(10)->get();

        $totalDeliveries = $deliveryMan->deliveries()->count();
        $totalCommission = $deliveryMan->commissions()->sum('commission_amount');

        $chartData = $this->getDeliveryManChartData($period, $startDate, $endDate, $deliveryMan->id);

        return view('backend.delivery_management.delivery_man.dashboard', compact(
            'deliveryMan',
            'totalOrders',
            'completedOrders',
            'pendingOrders',
            'cancelledOrders',
            'todayOrders',
            'todayCollection',
            'todayDue',
            'weekOrders',
            'weekCollection',
            'monthOrders',
            'monthCollection',
            'totalCollection',
            'totalDue',
            'recentOrders',
            'totalDeliveries',
            'totalCommission',
            'chartData',
            'period'
        ));
    }

    private function getDeliveryManChartData($period, $startDate, $endDate, $deliveryManId)
    {
        $labels = [];
        $ordersData = [];
        $collectionData = [];
        $dueData = [];

        $query = FieldOrder::query()->where('delivery_man_id', $deliveryManId);

        if ($period === 'today') {
            for ($i = 0; $i < 24; $i++) {
                $labels[] = date('h A', mktime($i, 0, 0));
                $ordersData[] = (clone $query)->whereDate('created_at', date('Y-m-d'))
                    ->whereRaw('HOUR(created_at) = ?', [$i])
                    ->count();
                $collectionData[] = (clone $query)->whereDate('created_at', date('Y-m-d'))
                    ->whereRaw('HOUR(created_at) = ?', [$i])
                    ->sum('paid_amount');
                $dueData[] = (clone $query)->whereDate('created_at', date('Y-m-d'))
                    ->whereRaw('HOUR(created_at) = ?', [$i])
                    ->sum('due_amount');
            }
        } elseif ($period === 'week') {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $interval = new \DateInterval('P1D');
            $daterange = new \DatePeriod($start, $interval, $end);

            foreach ($daterange as $date) {
                $labels[] = $date->format('D');
                $dateStr = $date->format('Y-m-d');
                $ordersData[] = (clone $query)->whereDate('created_at', $dateStr)->count();
                $collectionData[] = (clone $query)->whereDate('created_at', $dateStr)->sum('paid_amount');
                $dueData[] = (clone $query)->whereDate('created_at', $dateStr)->sum('due_amount');
            }
        } else {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $interval = new \DateInterval('P1D');
            $daterange = new \DatePeriod($start, $interval, $end);

            foreach ($daterange as $date) {
                $labels[] = $date->format('d M');
                $dateStr = $date->format('Y-m-d');
                $ordersData[] = (clone $query)->whereDate('created_at', $dateStr)->count();
                $collectionData[] = (clone $query)->whereDate('created_at', $dateStr)->sum('paid_amount');
                $dueData[] = (clone $query)->whereDate('created_at', $dateStr)->sum('due_amount');
            }
        }

        return [
            'labels' => $labels,
            'orders' => $ordersData,
            'collection' => $collectionData,
            'due' => $dueData,
        ];
    }

    public function logout(Request $request)
    {
        Auth::guard('delivery_man')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('delivery-man.login');
    }
}
