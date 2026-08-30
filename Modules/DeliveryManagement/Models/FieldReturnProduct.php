<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class FieldReturnProduct extends Model
{
    protected $fillable = [
        'field_return_id', 'product_id', 'product_variant_id', 'product_batch_id',
        'code', 'name', 'qty', 'unit_price', 'sub_total', 'note', 'photo'
    ];

    public function fieldReturn()
    {
        return $this->belongsTo(FieldReturn::class);
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
