<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderSettingsToEcommerceSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('ecommerce_settings', function (Blueprint $table) {
            $table->tinyInteger('active_place_order')->default(1)->after('flat_rate_shipping');
            $table->tinyInteger('active_whatsapp_order')->default(0)->after('active_place_order');
        });
    }

    public function down()
    {
        Schema::table('ecommerce_settings', function (Blueprint $table) {
            $table->dropColumn(['active_place_order', 'active_whatsapp_order']);
        });
    }
}
