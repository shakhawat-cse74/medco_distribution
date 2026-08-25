<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_requests')) {
            Schema::create('purchase_requests', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->unique();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('supplier_id');
                $table->integer('item')->default(0);
                $table->double('total_qty')->default(0);
                $table->double('total_discount')->default(0);
                $table->double('total_tax')->default(0);
                $table->double('total_cost')->default(0);
                $table->double('order_tax_rate')->nullable()->default(0);
                $table->double('order_tax')->nullable()->default(0);
                $table->double('order_discount')->nullable()->default(0);
                $table->double('shipping_cost')->nullable()->default(0);
                $table->double('grand_total')->default(0);
                $table->tinyInteger('status')->default(1)->comment('1: Pending, 2: Ordered/Sent, 3: Completed/Converted, 4: Cancelled');
                $table->string('document')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('product_purchase_requests')) {
            Schema::create('product_purchase_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_request_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->unsignedBigInteger('product_batch_id')->nullable();
                $table->unsignedBigInteger('purchase_unit_id')->nullable();
                $table->double('qty')->default(0);
                $table->double('recieved_qty')->nullable()->default(0);
                $table->double('net_unit_cost')->default(0);
                $table->double('discount')->default(0);
                $table->double('tax_rate')->default(0);
                $table->double('tax')->default(0);
                $table->double('total')->default(0);
                $table->timestamps();

                $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_purchase_requests');
        Schema::dropIfExists('purchase_requests');
    }
};
