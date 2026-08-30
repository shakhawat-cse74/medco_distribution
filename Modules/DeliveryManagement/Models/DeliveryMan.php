<?php

namespace Modules\DeliveryManagement\Models;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryMan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone_number', 'password', 'address', 'city', 'country',
        'nid_number', 'license_number', 'vehicle_type', 'vehicle_number', 'image',
        'user_id', 'warehouse_id', 'note', 'is_active', 'last_login_at', 'fcm_token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignments()
    {
        return $this->hasMany(DeliveryManAssignment::class);
    }

    public function vehicles()
    {
        return $this->hasMany(DeliveryManVehicle::class);
    }

    public function deliveries()
    {
        return $this->hasMany(DeliveryManDelivery::class);
    }

    public function fieldOrders()
    {
        return $this->hasMany(FieldOrder::class, 'delivery_man_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(FieldPayment::class, FieldOrder::class);
    }

    public function commissions()
    {
        return $this->hasMany(DeliveryManCommission::class);
    }

    public function cashDeposits()
    {
        return $this->hasMany(CashDeposit::class);
    }

    public function visits()
    {
        return $this->hasMany(CustomerVisit::class);
    }

    public function schedules()
    {
        return $this->hasMany(DeliveryManSchedule::class);
    }

    public function returns()
    {
        return $this->hasMany(FieldReturn::class);
    }
}
