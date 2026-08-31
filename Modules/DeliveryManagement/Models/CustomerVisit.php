<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerVisit extends Model
{
    protected $fillable = [
        'delivery_man_id', 'customer_id', 'check_in_at', 'check_out_at',
        'check_in_latitude', 'check_in_longitude', 'check_out_latitude',
        'check_out_longitude', 'note'
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
