<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(Database\Seeders\Tenant\TenantDatabaseSeeder::class);
        $this->call(Database\Seeders\UserSeeder::class);
        $this->call(Database\Seeders\DeliveryManagementTableSeeder::class);
    }
}
