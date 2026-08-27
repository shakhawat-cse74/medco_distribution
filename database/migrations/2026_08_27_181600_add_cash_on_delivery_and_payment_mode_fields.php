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
        if (Schema::hasTable('ecommerce_settings')) {
            Schema::table('ecommerce_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('ecommerce_settings', 'cash_on_delivery')) {
                    $table->boolean('cash_on_delivery')->default(1);
                }
            });
        }

        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                if (!Schema::hasColumn('sales', 'payment_mode')) {
                    $table->string('payment_mode', 50)->nullable()->default(null);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ecommerce_settings')) {
            Schema::table('ecommerce_settings', function (Blueprint $table) {
                if (Schema::hasColumn('ecommerce_settings', 'cash_on_delivery')) {
                    $table->dropColumn('cash_on_delivery');
                }
            });
        }

        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                if (Schema::hasColumn('sales', 'payment_mode')) {
                    $table->dropColumn('payment_mode');
                }
            });
        }
    }
};
