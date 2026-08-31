<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_man_assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('delivery_man_id');
            $table->integer('warehouse_id')->nullable();
            $table->integer('route_id')->nullable();
            $table->integer('area_id')->nullable();
            $table->text('territory_ids')->nullable();
            $table->text('customer_ids')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->unique(['delivery_man_id', 'warehouse_id', 'route_id', 'area_id'], 'dm_assignment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_assignments');
    }
};
