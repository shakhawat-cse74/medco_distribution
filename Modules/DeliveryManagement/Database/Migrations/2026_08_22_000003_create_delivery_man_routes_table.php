<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_man_routes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->integer('warehouse_id')->nullable();
            $table->text('area_ids')->nullable();
            $table->text('customer_ids')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_routes');
    }
};
