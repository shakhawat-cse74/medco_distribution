<?php

namespace Modules\DeliveryManagement\Database\Seeders;

use Illuminate\Database\Seeders\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DeliveryManagementPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions for Delivery Sale module
        $deliverySalePermissions = [
            'delivery-sales-index',
            'delivery-sales-add',
            'delivery-sales-edit',
            'delivery-sales-delete',
            'delivery-sales-pos',
            'delivery-sales-gift-card-list',
            'delivery-sales-challan-list',
            'delivery-sales-challan-slip-list',
            'delivery-sales-packing-slip-list',
            'delivery-sales-sale-return',
            'delivery-sales-installment-list',
            'delivery-sales-coupon-list',
            'delivery-sales-cupon-list',
            'delivery-sales-courier-list',
            'delivery-sales-curirer-list',
            'delivery-sales-delivery-list',
            'delivery-sales-sale-exchange',
        ];

        // Create permissions for Delivery Installment module
        $deliveryInstallmentPermissions = [
            'delivery-installments-index',
            'delivery-installments-add',
            'delivery-installments-edit',
            'delivery-installments-delete',
        ];

        // Get or create super admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
            'description' => 'Super Administrator with full access',
        ]);

        // Create and sync permissions to super admin role
        foreach ($deliverySalePermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
                'description' => 'Permission for ' . str_replace('-', ' ', $permissionName),
            ]);
        }

        foreach ($deliveryInstallmentPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
                'description' => 'Permission for ' . str_replace('-', ' ', $permissionName),
            ]);
        }

        // Sync all permissions to super admin role
        $allPermissions = Permission::where('guard_name', 'web')->get();
        $superAdminRole->syncPermissions($allPermissions);

        // Also assign permissions to existing Delivery Man role
        $deliveryManRole = Role::firstOrCreate([
            'name' => 'Delivery Man',
            'guard_name' => 'web',
            'description' => 'Delivery Man with delivery-specific permissions',
        ]);

        // Delivery Man gets limited permissions
        $deliveryManPermissions = [
            'delivery-sales-index',
            'delivery-sales-pos',
            'delivery-sales-gift-card-list',
            'delivery-sales-challan-list',
            'delivery-sales-challan-slip-list',
            'delivery-sales-packing-slip-list',
            'delivery-sales-sale-return',
            'delivery-sales-installment-list',
            'delivery-sales-coupon-list',
            'delivery-sales-cupon-list',
            'delivery-sales-courier-list',
            'delivery-sales-curirer-list',
            'delivery-sales-delivery-list',
            'delivery-sales-sale-exchange',
        ];

        $deliveryManRole->syncPermissions($deliveryManPermissions);

        // Get or create Sales role
        $salesRole = Role::firstOrCreate([
            'name' => 'Sales',
            'guard_name' => 'web',
            'description' => 'Sales person with sales-related permissions',
        ]);

        // Sales gets even more limited permissions
        $salesPermissions = [
            'delivery-sales-index',
            'delivery-sales-pos',
        ];

        $salesRole->syncPermissions($salesPermissions);
    }
}
