<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateDeliveryMan
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('delivery_man')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('delivery-man.login');
        }

        return $next($request);
    }
}
