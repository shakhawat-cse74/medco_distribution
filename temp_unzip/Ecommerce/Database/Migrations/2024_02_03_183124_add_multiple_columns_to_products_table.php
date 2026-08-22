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
            $table->text('short_description')->after('product_details')->nullable();
            $table->text('specification')->after('short_description')->nullable();
            $table->longText('related_products')->after('meta_description')->nullable();
            $table->tinyInteger('track_inventory')->after('in_stock')->default(0);
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
