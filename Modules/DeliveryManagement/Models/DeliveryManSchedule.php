<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryManSchedule extends Model
{
    protected $fillable = [
        'delivery_man_id', 'day_of_week', 'specific_date', 'start_time',
        'end_time', 'is_active', 'note'
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }
}
