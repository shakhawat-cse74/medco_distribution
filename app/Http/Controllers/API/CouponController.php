<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    use ApiResponse;

    /**
     * List Available Active Coupons
     */
    public function index(Request $request)
    {
        try {
            $coupons = Coupon::active()
                ->where(function ($q) {
                    $q->where('quantity', 0)->orWhereRaw('used < quantity');
                })
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn($c) => $this->formatCoupon($c));

            return $this->success([
                'coupons' => $coupons,
            ], 'Available coupons retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve coupons: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validate Coupon Code and Calculate Discount
     */
    public function validateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string',
            'subtotal'    => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $code = trim($request->coupon_code);
            $subtotal = (float) ($request->subtotal ?? 0);

            $coupon = Coupon::where('code', $code)->first();

            if (!$coupon) {
                return $this->badRequest('Coupon not found or invalid.');
            }

            if (!$coupon->is_active) {
                return $this->badRequest('This coupon is no longer active.');
            }

            if ($coupon->expired_date && $coupon->expired_date->format('Y-m-d') < date('Y-m-d')) {
                return $this->badRequest('This coupon has expired on ' . $coupon->expired_date->format('d M Y') . '.');
            }

            if ($coupon->quantity > 0 && $coupon->used >= $coupon->quantity) {
                return $this->badRequest('Coupon usage limit reached.');
            }

            if ($subtotal > 0 && $coupon->minimum_amount && $subtotal < (float)$coupon->minimum_amount) {
                return $this->badRequest(
                    'Minimum spend of ' . number_format($coupon->minimum_amount, 2) . ' required to use this coupon.',
                    [
                        'coupon'          => $this->formatCoupon($coupon),
                        'minimum_amount'  => (float) $coupon->minimum_amount,
                        'current_subtotal'=> $subtotal,
                        'needed_amount'   => round($coupon->minimum_amount - $subtotal, 2),
                    ]
                );
            }

            $discount = $subtotal > 0 ? $coupon->calculateDiscount($subtotal) : (float)$coupon->amount;

            return $this->success([
                'coupon'          => $this->formatCoupon($coupon),
                'subtotal'        => round($subtotal, 2),
                'discount_amount' => round($discount, 2),
                'final_total'     => round(max(0, $subtotal - $discount), 2),
            ], 'Coupon is valid');
        } catch (\Throwable $e) {
            return $this->error('Failed to validate coupon: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format Coupon JSON
     */
    protected function formatCoupon(Coupon $coupon): array
    {
        return [
            'id'             => $coupon->id,
            'code'           => $coupon->code,
            'type'           => $coupon->type, // 'percentage' or 'fixed'
            'amount'         => (float) $coupon->amount,
            'minimum_amount' => (float) $coupon->minimum_amount,
            'expired_date'   => $coupon->expired_date ? $coupon->expired_date->format('Y-m-d') : null,
            'is_active'      => (bool) $coupon->is_active,
        ];
    }
}
