<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEcommerceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_settings', function (Blueprint $table) {
            $table->string('theme')->after('site_title')->default('default');
            $table->string('theme_font')->after('theme')->default('Inter');
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
            $table->dropColumn('theme');
            $table->dropColumn('theme_font');
        });
    }
}
