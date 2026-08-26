<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryManRoute extends Model
{
    protected $fillable = [
        'name', 'code', 'warehouse_id', 'area_ids', 'customer_ids',
        'description', 'is_active', 'created_by'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignments()
    {
        return $this->hasMany(DeliveryManAssignment::class);
    }
}
