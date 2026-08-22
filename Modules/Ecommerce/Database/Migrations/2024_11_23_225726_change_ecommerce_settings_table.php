<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeEcommerceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_settings', function (Blueprint $table) {
            $table->longText('custom_css')->nullable()->change();
            $table->longText('custom_js')->nullable()->change();
            $table->text('chat_code')->nullable()->change();
            $table->text('analytics_code')->nullable()->change();
            $table->text('fb_pixel_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_settings', function (Blueprint $table) {
            $table->string('custom_css')->nullable()->change();
            $table->string('custom_js')->nullable()->change();
            $table->string('chat_code')->nullable()->change();
            $table->string('analytics_code')->nullable()->change();
            $table->string('fb_pixel_code')->nullable()->change();
        });
    }
}
