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
        Schema::table('products', function (Blueprint $table) {
            $table->double('product_lowest_price')->nullable();
            $table->double('product_average_price')->nullable();
            $table->double('product_highest_price')->nullable();
            $table->double('wholesale_lowest_price')->nullable();
            $table->double('wholesale_average_price')->nullable();
            $table->double('wholesale_highest_price')->nullable();
            $table->double('wholesale_min_qty')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_lowest_price',
                'product_average_price',
                'product_highest_price',
                'wholesale_lowest_price',
                'wholesale_average_price',
                'wholesale_highest_price',
                'wholesale_min_qty'
            ]);
        });
    }
};
