<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{
    protected $table = 'delivery_areas';

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
        'delivery_charge' => 'float',
        'estimated_days'  => 'integer',
        'is_active'       => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
