<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditPageWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('page_widgets', function (Blueprint $table) {
            $table->renameColumn('3c_banner_link1', 'three_c_banner_link1');
            $table->renameColumn('3c_banner_image1', 'three_c_banner_image1');
            $table->renameColumn('3c_banner_link2', 'three_c_banner_link2');
            $table->renameColumn('3c_banner_image2', 'three_c_banner_image2');
            $table->renameColumn('3c_banner_link3', 'three_c_banner_link3');
            $table->renameColumn('3c_banner_image3', 'three_c_banner_image3');

            $table->renameColumn('2c_banner_link1', 'two_c_banner_link1');
            $table->renameColumn('2c_banner_image1', 'two_c_banner_image1');
            $table->renameColumn('2c_banner_link2', 'two_c_banner_link2');
            $table->renameColumn('2c_banner_image2', 'two_c_banner_image2');

            $table->renameColumn('1c_banner_link1', 'one_c_banner_link1');
            $table->renameColumn('1c_banner_image1', 'one_c_banner_image1');
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
