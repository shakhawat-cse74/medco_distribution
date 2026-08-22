<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultipleColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() 
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'short_description')) $table->text('short_description')->after('product_details')->nullable();
            if (!Schema::hasColumn('products', 'specification')) $table->text('specification')->after('short_description')->nullable();
            if (!Schema::hasColumn('products', 'related_products')) $table->longText('related_products')->after('meta_description')->nullable();
            if (!Schema::hasColumn('products', 'track_inventory')) $table->tinyInteger('track_inventory')->after('in_stock')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {

        });
    }
}
