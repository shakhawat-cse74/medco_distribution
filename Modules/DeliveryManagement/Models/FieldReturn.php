<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_no', 'field_order_id', 'delivery_man_id', 'customer_id',
        'reason', 'status', 'note', 'refund_amount', 'created_by'
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

    public function products()
    {
        return $this->hasMany(FieldReturnProduct::class);
    }
}
