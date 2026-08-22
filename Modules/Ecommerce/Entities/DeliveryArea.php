<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{
    protected $fillable = [
        'name',
        'city',
        'zone',
        'delivery_charge',
        'estimated_days',
        'is_active',
        'note',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'delivery_charge' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
