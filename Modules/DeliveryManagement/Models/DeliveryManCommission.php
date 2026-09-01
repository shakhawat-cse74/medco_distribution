<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryManCommission extends Model
{
    protected $fillable = [
        'delivery_man_id', 'field_order_id', 'commission_type', 'commission_rate',
        'order_amount', 'commission_amount', 'status', 'paid_at', 'note', 'created_by',
        'target_orders', 'target_amount', 'bonus_amount', 'incentive_type'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    public function fieldOrder()
    {
        return $this->belongsTo(FieldOrder::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('commission_type', $type);
    }
}
