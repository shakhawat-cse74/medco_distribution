<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class CashDeposit extends Model
{
    protected $fillable = [
        'delivery_man_id', 'amount', 'deposit_method', 'bank_name', 'account_number',
        'reference_no', 'slip_file', 'status', 'note', 'verified_by', 'verified_at', 'created_by'
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class);
    }
}
