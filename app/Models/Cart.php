<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'guest_token',
        'product_id',
        'variant_id',
        'qty',
        'unit_price',
    ];

    protected $casts = [
        'user_id'    => 'integer',
        'product_id' => 'integer',
        'variant_id' => 'integer',
        'qty'        => 'float',
        'unit_price' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Scope for filtering by user or guest token
     */
    public function scopeForCustomer($query, ?int $userId, ?string $guestToken)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        if ($guestToken) {
            return $query->where('guest_token', $guestToken);
        }

        return $query->whereRaw('1 = 0');
    }
}
