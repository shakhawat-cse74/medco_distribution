<?php

namespace Modules\DeliveryManagement\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryProof extends Model
{
    protected $fillable = [
        'delivery_id', 'proof_type', 'file_path', 'signature_data',
        'otp_code', 'is_verified', 'verified_at', 'note'
    ];

    public function delivery()
    {
        return $this->belongsTo(DeliveryManDelivery::class);
    }
}
