<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_man_commissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('delivery_man_id');
            $table->integer('field_order_id')->nullable();
            $table->string('commission_type')->default('percentage');
            $table->decimal('commission_rate', 10, 2)->default(0);
            $table->decimal('order_amount', 20, 2)->default(0);
            $table->decimal('commission_amount', 20, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_commissions');
    }
};
