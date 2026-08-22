<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Ensure General Settings exists
        $generalSetting = DB::table('general_settings')->first();
        if (!$generalSetting) {
            DB::table('general_settings')->insert([
                'site_title' => 'BanglaSoft',
                'site_logo' => 'banglasoft_logo.png',
                'is_rtl' => 0,
                'currency' => '$',
                'currency_position' => 'prefix',
                'staff_access' => 'own',
                'without_stock' => 'no',
                'date_format' => 'd/m/Y',
                'developed_by' => 'BanglaSoft',
                'invoice_format' => 'standard',
                'decimal' => 2,
                'state' => 1,
                'theme' => 'default.css',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Ensure Admin User with username 'admin' and password '12345678'
        $admin = DB::table('users')->where('name', 'admin')->orWhere('email', 'admin@gmail.com')->first();

        if ($admin) {
            DB::table('users')->where('id', $admin->id)->update([
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
                'role_id' => 1,
                'is_active' => 1,
                'is_deleted' => 0,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->insert([
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
                'phone' => '1234567890',
                'company_name' => 'POS',
                'role_id' => 1,
                'service_staff' => 0,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Clear cached general setting
        Cache::forget('general_setting');
    }
}
