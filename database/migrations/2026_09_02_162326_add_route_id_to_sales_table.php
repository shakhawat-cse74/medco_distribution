<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
    */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'route_id')) {
                $table->unsignedBigInteger('route_id')->nullable()->after('delivery_man_id');
            }
        });

        try {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreign('route_id')->references('id')->on('delivery_areas')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // Foreign key already exists or cannot be created
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            try {
                $table->dropForeign(['route_id']);
            } catch (\Throwable $e) {}

            if (Schema::hasColumn('sales', 'route_id')) {
                $table->dropColumn('route_id');
            }
        });
    }
};
