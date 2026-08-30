<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_returns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_no')->unique()->nullable();
            $table->integer('field_order_id');
            $table->integer('delivery_man_id');
            $table->integer('customer_id')->nullable();
            $table->string('reason');
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->decimal('refund_amount', 20, 2)->default(0);
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_returns');
    }
};
