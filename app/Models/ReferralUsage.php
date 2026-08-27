<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralUsage extends Model
{
    protected $table = 'referral_usages';

    protected $fillable = [
        'referrer_user_id',
        'referred_user_id',
        'referral_code',
        'bonus_given',
        'referral_bonus',
        'used_at',
    ];

    protected $casts = [
        'bonus_given' => 'boolean',
        'used_at' => 'datetime',
        'referral_bonus' => 'decimal:2',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
