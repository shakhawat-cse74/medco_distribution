<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Warehouse;

class DeliveryManAssignment extends Model
{
    protected $fillable = [
        'delivery_man_id', 'warehouse_id', 'route_id', 'area_id',
        'territory_ids', 'customer_ids', 'is_primary', 'created_by'
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function route()
    {
        return $this->belongsTo(DeliveryManRoute::class);
    }
}
