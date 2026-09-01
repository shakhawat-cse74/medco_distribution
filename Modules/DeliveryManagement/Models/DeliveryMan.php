<?php

namespace Modules\DeliveryManagement\Models;

use App\Models\User;
use App\Models\Warehouse;
use Modules\Ecommerce\Entities\DeliveryArea;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class DeliveryMan extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use SoftDeletes;

    protected $fillable = [
        'delivery_man_id', 'name', 'address', 'city', 'country',
        'nid_number', 'image', 'user_id', 'last_login_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignments()
    {
        return $this->hasMany(DeliveryManAssignment::class);
    }

    public function routes()
    {
        return $this->belongsToMany(DeliveryArea::class, 'delivery_men_routes', 'delivery_man_id', 'route_id');
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
