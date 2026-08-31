<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryManRoute extends Model
{
    protected $table = 'delivery_areas';

    protected $fillable = [
        'name', 'city', 'zone', 'delivery_charge', 'estimated_days', 'is_active', 'note'
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'delivery_charge' => 'decimal:2',
    ];
}
