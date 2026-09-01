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
            $table->unsignedBigInteger('installment_parent_id')->nullable()->after('route_id');
            $table->decimal('installment_amount', 15, 2)->nullable()->after('installment_parent_id');
            $table->integer('installment_months')->nullable()->after('installment_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['installment_parent_id', 'installment_amount', 'installment_months']);
        });
    }
};
