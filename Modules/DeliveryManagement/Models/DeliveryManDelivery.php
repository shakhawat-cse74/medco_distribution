<?php

namespace Modules\DeliveryManagement\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class DeliveryManDelivery extends Model
{
    protected $fillable = [
        'reference_no', 'field_order_id', 'delivery_man_id', 'customer_id',
        'address', 'city', 'country', 'latitude', 'longitude', 'status', 'priority',
        'assigned_by', 'assigned_at', 'started_at', 'completed_at', 'note'
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function fieldOrder()
    {
        return $this->belongsTo(FieldOrder::class);
    }

    public function proofs()
    {
        return $this->hasMany(DeliveryProof::class);
    }
}
