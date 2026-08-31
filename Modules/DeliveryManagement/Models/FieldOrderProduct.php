<?php

namespace Modules\DeliveryManagement\Models;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class FieldOrderProduct extends Model
{
    protected $fillable = [
        'field_order_id', 'product_id', 'product_variant_id', 'product_batch_id',
        'code', 'name', 'unit', 'qty', 'sale_unit_quantity', 'unit_price', 'sub_total',
        'discount_amount', 'discount_type', 'tax_amount', 'note'
    ];

    public function fieldOrder()
    {
        return $this->belongsTo(FieldOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
