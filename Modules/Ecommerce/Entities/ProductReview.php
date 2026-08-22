<?php

namespace Modules\Ecommerce\Entities;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductReview extends Model
{
    use HasFactory;

      protected $fillable = [
        'product_id',
        'customer_id',
        'customer_name',
        'rating',
        'review',
        'approved',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
