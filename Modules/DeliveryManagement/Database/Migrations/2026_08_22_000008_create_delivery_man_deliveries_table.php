<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_man_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_no')->unique()->nullable();
            $table->integer('field_order_id')->nullable();
            $table->integer('delivery_man_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('status')->default('pending');
            $table->string('priority')->default('normal');
            $table->integer('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_deliveries');
    }
};
