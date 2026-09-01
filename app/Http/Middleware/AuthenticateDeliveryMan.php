<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\DeliveryManagement\Models\DeliveryMan;

class AuthenticateDeliveryMan
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('web')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('delivery-man.login');
        }

        $user = Auth::guard('web')->user();
        
        // Check if user has Delivery Man role
        $deliveryManRole = \Spatie\Permission\Models\Role::where('name', 'Delivery Man')->first();
        if (!$deliveryManRole || $user->role_id != $deliveryManRole->id) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            return redirect()->route('delivery-man.login')->with('not_permitted', __('db.Sorry! You are not allowed to access this panel'));
        }

        // Check if delivery man profile exists and is active
        $deliveryMan = DeliveryMan::where('user_id', $user->id)->first();
        if (!$deliveryMan || !$deliveryMan->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            return redirect()->route('delivery-man.login')->with('not_permitted', __('db.Sorry! Your account is inactive'));
        }

        // Share delivery man data with views
        view()->share('auth_delivery_man', $deliveryMan);

        return $next($request);
    }
}
