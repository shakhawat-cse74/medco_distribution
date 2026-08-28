<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class DeliveryManMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $deliveryManRole = Role::where('name', 'Delivery Man')->first();
        if (!$deliveryManRole || $user->role_id != $deliveryManRole->id) {
            return redirect('/')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        // Check if user has an active delivery man profile
        $deliveryMan = \Modules\DeliveryManagement\Models\DeliveryMan::where('user_id', $user->id)->first();
        if (!$deliveryMan) {
            return redirect('/')->with('not_permitted', __('db.Sorry! Delivery man profile not found'));
        }

        // Share delivery man data with all views
        view()->share('auth_delivery_man', $deliveryMan);

        return $next($request);
    }
}
