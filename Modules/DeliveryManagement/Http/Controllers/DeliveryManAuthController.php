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

    public function dashboard()
    {
        $deliveryMan = Auth::guard('delivery_man')->user();

        $totalOrders = $deliveryMan->fieldOrders()->count();
        $completedOrders = $deliveryMan->fieldOrders()->where('status', 'completed')->count();
        $pendingOrders = $deliveryMan->fieldOrders()->where('status', 'pending')->count();
        $cancelledOrders = $deliveryMan->fieldOrders()->where('status', 'cancelled')->count();

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
            'recentOrders'
        ));
    }

    public function logout(Request $request)
    {
        Auth::guard('delivery_man')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('delivery-man.login');
    }
}
