<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryManVehicle extends Model
{
    protected $fillable = [
        'delivery_man_id', 'vehicle_type', 'vehicle_number', 'brand', 'model',
        'color', 'registration_number', 'license_number', 'registration_expiry',
        'insurance_expiry', 'image', 'note'
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }
}
