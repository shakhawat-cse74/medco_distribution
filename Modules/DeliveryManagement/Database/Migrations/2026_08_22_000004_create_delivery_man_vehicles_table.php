<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_man_vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('delivery_man_id');
            $table->string('vehicle_type');
            $table->string('vehicle_number')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('license_number')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_vehicles');
    }
};
