<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CretePageWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('page_id');
            $table->string('order');

            $table->string('product_category_title')->nullable();
            $table->string('product_category_id')->nullable();
            $table->string('product_category_type')->nullable();
            $table->string('product_category_slider_loop')->nullable();
            $table->string('product_category_slider_autoplay')->nullable();
            $table->string('product_category_slider_autoplay_speed')->nullable();
            $table->string('product_category_limit')->nullable();

            $table->string('tab_product_category_id')->nullable();
            $table->string('tab_product_category_type')->nullable();
            $table->string('tab_product_category_slider_loop')->nullable();
            $table->string('tab_product_category_slider_autoplay')->nullable();
            $table->string('tab_product_category_slider_autoplay_speed')->nullable();
            $table->string('tab_product_category_limit')->nullable();

            $table->string('product_collection_title')->nullable();
            $table->string('product_collection_id')->nullable();
            $table->string('product_collection_type')->nullable();
            $table->string('product_collection_slider_loop')->nullable();
            $table->string('product_collection_slider_autoplay')->nullable();
            $table->string('product_collection_slider_autoplay_speed')->nullable();
            $table->string('product_collection_limit')->nullable();

            $table->string('category_slider_title')->nullable();
            $table->string('category_slider_loop')->nullable();
            $table->string('category_slider_autoplay')->nullable();
            $table->string('category_slider_autoplay_speed')->nullable();
            $table->string('category_slider_ids')->nullable();

            $table->string('brand_slider_title')->nullable();
            $table->string('brand_slider_loop')->nullable();
            $table->string('brand_slider_autoplay')->nullable();
            $table->string('brand_slider_autoplay_speed')->nullable();
            $table->string('brand_slider_ids')->nullable();

            $table->string('3c_banner_link1')->nullable();
            $table->string('3c_banner_image1')->nullable();
            $table->string('3c_banner_link2')->nullable();
            $table->string('3c_banner_image2')->nullable();
            $table->string('3c_banner_link3')->nullable();
            $table->string('3c_banner_image3')->nullable();

            $table->string('2c_banner_link1')->nullable();
            $table->string('2c_banner_image1')->nullable();
            $table->string('2c_banner_link2')->nullable();
            $table->string('2c_banner_image2')->nullable();

            $table->string('1c_banner_link1')->nullable();
            $table->string('1c_banner_image1')->nullable();

            $table->string('text_title')->nullable();
            $table->string('text_content')->nullable();
            
            $table->timestamps();
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
