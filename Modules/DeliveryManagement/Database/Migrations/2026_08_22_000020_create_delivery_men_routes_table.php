<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_men_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_man_id');
            $table->unsignedBigInteger('route_id');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('delivery_man_id')->references('id')->on('delivery_men')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('delivery_areas')->onDelete('cascade');
            $table->unique(['delivery_man_id', 'route_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_men_routes');
    }
};
