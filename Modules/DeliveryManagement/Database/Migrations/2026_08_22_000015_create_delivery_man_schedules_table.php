<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_man_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('delivery_man_id');
            $table->string('day_of_week')->nullable();
            $table->date('specific_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_schedules');
    }
};
