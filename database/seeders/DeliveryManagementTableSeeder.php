<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DeliveryManagementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DeliveryManagementModuleSeeder::class,
        ]);
    }
}