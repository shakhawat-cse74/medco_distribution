<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipleColumnToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('tags')->nullable()->after('slug');
            $table->string('meta_title')->nullable()->after('product_details');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->tinyInteger('is_online')->nullable()->after('featured');
            $table->tinyInteger('in_stock')->nullable()->after('is_online');
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
            $table->dropColumn(['slug', 'tags','meta_title','meta_description']);
        });
    }
}
