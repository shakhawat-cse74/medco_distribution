<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
        'phone',
        'company_name',
        'role_id',
        'biller_id',
        'warehouse_id',
        'kitchen_id',
        'service_staff',
        'is_active',
        'is_deleted',
        'email_verified_at',
        'otp',
        'otp_created_at',
        'avatar',
        'device_id',
        'is_guest',
        'fcm_token',
        'apple_id',
        'notification',
        'messages',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_created_at' => 'datetime',
        'is_guest' => 'boolean',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'notification' => 'boolean',
        'messages' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_deleted' => false,
        'is_guest' => false,
        'notification' => true,
        'messages' => true,
    ];

    protected $appends = [
        'avatar_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if ($user->is_deleted === null) {
                $user->is_deleted = false;
            }
            if ($user->is_active === null) {
                $user->is_active = true;
            }
        });
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
                return $this->avatar;
            }
            return asset('uploads/avatars/' . $this->avatar);
        }
        return asset('images/user/default.png');
    }

    public function isActive()
    {
        return (bool) $this->is_active;
    }

    public function holiday() {
        return $this->hasMany('App\Models\Holiday');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function role()
    {
        return $this->belongsTo(Roles::class, 'role_id');
    }
}

