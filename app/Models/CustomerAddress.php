<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'country',
        'zip',
        'default',
    ];

    protected $attributes = [
        'country' => 'Bangladesh',
        'default' => 0,
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'default'     => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function scopeDefault($query)
    {
        return $query->where('default', 1);
    }
}
