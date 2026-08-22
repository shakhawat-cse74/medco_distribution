<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEcommerceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_title')->nullable(); 
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('store_phone')->nullable();
            $table->string('store_email')->nullable();
            $table->string('store_address')->nullable();
            $table->bigInteger('home_page')->nullable();
            $table->string('contact_form_email')->nullable();
            $table->double('free_shipping_from')->nullable();
            $table->double('flat_rate_shipping')->nullable();
            $table->json('checkout_pages')->nullable();
            $table->string('custom_css')->nullable();
            $table->string('custom_js')->nullable();
            $table->string('chat_code')->nullable();
            $table->string('analytics_code')->nullable();
            $table->string('fb_pixel_code')->nullable();
            $table->integer('sell_without_stock')->default(0);
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
        Schema::dropIfExists('ecommerce_settings');
    }
}
