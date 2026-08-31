<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('field_order_id');
            $table->string('payment_method');
            $table->decimal('amount', 20, 2)->default(0);
            $table->string('reference_no')->nullable();
            $table->string('cheque_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('cheque_date')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('approval_code')->nullable();
            $table->integer('gift_card_id')->nullable();
            $table->integer('reward_point_id')->nullable();
            $table->decimal('reward_point_used', 20, 2)->default(0);
            $table->integer('created_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_payments');
    }
};
