<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class FieldPayment extends Model
{
    protected $fillable = [
        'field_order_id', 'payment_method', 'amount', 'reference_no', 'cheque_no',
        'bank_name', 'cheque_date', 'card_type', 'card_last_four', 'approval_code',
        'gift_card_id', 'reward_point_id', 'reward_point_used', 'created_by', 'note'
    ];

    public function fieldOrder()
    {
        return $this->belongsTo(FieldOrder::class);
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }
}
