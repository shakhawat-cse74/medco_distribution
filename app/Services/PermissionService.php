<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class PermissionService
{
    public function getAuthenticatedUserPermissions(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        // Isolate tenant space using tenant identifier prefixes
        $tenantPrefix = function_exists('tenant') && tenant('id') ? tenant('id') . ':' : '';
        $cacheKey = "{$tenantPrefix}role_has_permissions_list:{$user->role_id}";

        $permissions = Cache::remember($cacheKey, now()->addYear(), function () use ($user) {
            return DB::table('permissions')
                ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->where('role_id', $user->role_id)
                ->pluck('permissions.name')
                ->toArray();
        });

        if ($permissions instanceof Collection) {
            $permissions = $permissions->toArray();
        }

        return array_map(function ($item) {
            return is_object($item) ? ($item->name ?? '') : (string) $item;
        }, (array) $permissions);
    }
}