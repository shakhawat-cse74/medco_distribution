<?php

namespace Modules\DeliveryManagement\Models;

use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DeliveryManagement\Models\DeliveryMan;

class FieldOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_no', 'delivery_man_id', 'customer_id', 'warehouse_id', 'sale_id',
        'status', 'order_type', 'sub_total', 'discount_amount', 'discount_type',
        'tax_amount', 'shipping_cost', 'grand_total', 'paid_amount', 'due_amount',
        'coupon_ids', 'special_instructions', 'delivery_address', 'delivery_city',
        'delivery_country', 'delivery_latitude', 'delivery_longitude', 'invoice_no',
        'created_by', 'updated_by'
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function products()
    {
        return $this->hasMany(FieldOrderProduct::class);
    }

    public function payments()
    {
        return $this->hasMany(FieldPayment::class);
    }
}
