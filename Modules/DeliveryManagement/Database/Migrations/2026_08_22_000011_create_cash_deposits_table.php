<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_deposits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('delivery_man_id');
            $table->decimal('amount', 20, 2)->default(0);
            $table->string('deposit_method')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('slip_file')->nullable();
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->integer('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_deposits');
    }
};
