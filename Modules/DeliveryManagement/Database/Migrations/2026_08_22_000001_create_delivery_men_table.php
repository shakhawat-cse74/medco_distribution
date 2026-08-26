<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_men', function (Blueprint $table) {
            $table->increments('id');
            $table->string('delivery_man_id')->unique()->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone_number')->unique();
            $table->string('password')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('nid_number')->nullable();
            $table->string('license_number')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('image')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('warehouse_id')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('fcm_token')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_men');
    }
};
