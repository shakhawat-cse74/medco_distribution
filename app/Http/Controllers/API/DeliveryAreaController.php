<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DeliveryArea;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryAreaController extends Controller
{
    use ApiResponse;

    /**
     * List Active Delivery Areas & Shipping Settings
     */
    public function index(Request $request)
    {
        try {
            $areas = DeliveryArea::active()
                ->orderBy('name')
                ->get()
                ->map(fn($area) => [
                    'id'              => $area->id,
                    'name'            => $area->name,
                    'city'            => $area->city,
                    'zone'            => $area->zone,
                    'delivery_charge' => (float) $area->delivery_charge,
                    'estimated_days'  => (int) $area->estimated_days,
                    'note'            => $area->note,
                ]);

            $ecomSetting = DB::table('ecommerce_settings')->first();
            $freeShippingFrom = $ecomSetting ? (float)$ecomSetting->free_shipping_from : 0.0;
            $flatRateShipping = $ecomSetting ? (float)$ecomSetting->flat_rate_shipping : 0.0;

            return $this->success([
                'delivery_areas'         => $areas,
                'shipping_policy'        => [
                    'free_shipping_from' => $freeShippingFrom,
                    'flat_rate_shipping' => $flatRateShipping,
                ],
            ], 'Delivery areas retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve delivery areas: ' . $e->getMessage(), 500);
        }
    }
}
