<?php

namespace Modules\DeliveryManagement\Traits;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Modules\DeliveryManagement\Models\DeliveryMan;

trait ChecksDeliveryManRole
{
    protected function getDeliveryManRoleId(): ?int
    {
        $role = Role::where('name', 'Delivery Man')->first();
        return $role ? $role->id : null;
    }

    protected function isDeliveryManUser(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        $deliveryManRoleId = $this->getDeliveryManRoleId();
        return $deliveryManRoleId && $user->role_id == $deliveryManRoleId;
    }

    protected function getAuthDeliveryMan(): ?DeliveryMan
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return DeliveryMan::where('user_id', $user->id)->first();
    }
}
