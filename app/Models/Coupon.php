<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        "code", "type", "amount", "minimum_amount", "user_id", "quantity", "used", "expired_date", "is_active"
    ];

    protected $casts = [
        'amount'         => 'float',
        'minimum_amount' => 'float',
        'quantity'       => 'integer',
        'used'           => 'integer',
        'is_active'      => 'boolean',
        'expired_date'   => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1)
                     ->where('expired_date', '>=', date('Y-m-d'));
    }

    /**
     * Check if coupon is currently valid for a given subtotal
     */
    public function isValidForSubtotal(float $subtotal, ?string &$errorMsg = null): bool
    {
        if (!$this->is_active) {
            $errorMsg = 'Coupon is not active.';
            return false;
        }

        if ($this->expired_date && $this->expired_date->format('Y-m-d') < date('Y-m-d')) {
            $errorMsg = 'Coupon has expired.';
            return false;
        }

        if ($this->quantity > 0 && $this->used >= $this->quantity) {
            $errorMsg = 'Coupon usage limit has been reached.';
            return false;
        }

        if ($this->minimum_amount && $subtotal < (float) $this->minimum_amount) {
            $errorMsg = 'Minimum order amount for this coupon is ' . number_format($this->minimum_amount, 2);
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for a given subtotal
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $type = strtolower((string) $this->type);
        if ($type === 'percentage' || $type === 'percent') {
            $discount = ($subtotal * (float) $this->amount) / 100;
        } else {
            // Fixed amount
            $discount = (float) $this->amount;
        }

        return min($discount, $subtotal);
    }
}
