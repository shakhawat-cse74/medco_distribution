<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_no')->unique()->nullable();
            $table->integer('delivery_man_id');
            $table->integer('customer_id')->nullable();
            $table->integer('warehouse_id')->nullable();
            $table->integer('sale_id')->nullable();
            $table->string('status')->default('draft');
            $table->string('order_type')->default('field');
            $table->decimal('sub_total', 20, 2)->default(0);
            $table->decimal('discount_amount', 20, 2)->default(0);
            $table->string('discount_type')->default('flat');
            $table->decimal('tax_amount', 20, 2)->default(0);
            $table->decimal('shipping_cost', 20, 2)->default(0);
            $table->decimal('grand_total', 20, 2)->default(0);
            $table->decimal('paid_amount', 20, 2)->default(0);
            $table->decimal('due_amount', 20, 2)->default(0);
            $table->text('coupon_ids')->nullable();
            $table->text('special_instructions')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_country')->nullable();
            $table->string('delivery_latitude')->nullable();
            $table->string('delivery_longitude')->nullable();
            $table->string('invoice_no')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_orders');
    }
};
