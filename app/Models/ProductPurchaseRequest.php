<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPurchaseRequest extends Model
{
    use HasFactory;

    protected $table = 'product_purchase_requests';

    protected $fillable = [
        'purchase_request_id',
        'product_id',
        'variant_id',
        'product_batch_id',
        'purchase_unit_id',
        'qty',
        'recieved_qty',
        'net_unit_cost',
        'discount',
        'tax_rate',
        'tax',
        'total'
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }
}
