<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePageWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('page_widgets', function (Blueprint $table) {
            $table->renameColumn('tab_product_category_id', 'tab_product_collection_id');
            $table->renameColumn('tab_product_category_type', 'tab_product_collection_type');
            $table->renameColumn('tab_product_category_slider_loop', 'tab_product_collection_slider_loop');
            $table->renameColumn('tab_product_category_slider_autoplay', 'tab_product_collection_slider_autoplay');
            $table->renameColumn('tab_product_category_limit', 'tab_product_collection_limit');
            $table->dropColumn(['product_category_slider_autoplay_speed', 'tab_product_category_slider_autoplay_speed', 'product_collection_slider_autoplay_speed', 'category_slider_autoplay_speed', 'brand_slider_autoplay_speed']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
