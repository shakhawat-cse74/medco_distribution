<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreteWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->string('order');

            $table->string('feature_title')->nullable();
            $table->string('feature_secondary_title')->nullable();
            $table->string('feature_icon')->nullable();

            $table->string('site_info_name')->nullable();
            $table->string('site_info_description')->nullable();
            $table->string('site_info_address')->nullable();
            $table->string('site_info_phone')->nullable();
            $table->string('site_info_email')->nullable();
            $table->string('site_info_hours')->nullable();

            $table->string('newsletter_title')->nullable();
            $table->string('newsletter_text')->nullable();

            $table->string('quick_links_title')->nullable();
            $table->string('quick_links_menu')->nullable();

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
