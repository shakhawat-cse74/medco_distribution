<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_order_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('field_order_id');
            $table->integer('product_id');
            $table->integer('product_variant_id')->nullable();
            $table->integer('product_batch_id')->nullable();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('qty', 20, 2)->default(0);
            $table->decimal('sale_unit_quantity', 20, 2)->default(0);
            $table->decimal('unit_price', 20, 2)->default(0);
            $table->decimal('sub_total', 20, 2)->default(0);
            $table->decimal('discount_amount', 20, 2)->default(0);
            $table->string('discount_type')->default('flat');
            $table->decimal('tax_amount', 20, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_order_products');
    }
};
