<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultipleColumnsToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('billing_name')->nullable()->after('paid_amount');
            $table->string('billing_phone')->nullable()->after('billing_name');
            $table->string('billing_email')->nullable()->after('billing_phone');
            $table->string('billing_address')->nullable()->after('billing_email');
            $table->string('billing_city')->nullable()->after('billing_address');
            $table->string('billing_state')->nullable()->after('billing_city');
            $table->string('billing_country')->nullable()->after('billing_state');
            $table->string('billing_zip')->nullable()->after('billing_country');
            $table->string('shipping_name')->nullable()->after('billing_zip');
            $table->string('shipping_phone')->nullable()->after('shipping_name');
            $table->string('shipping_email')->nullable()->after('shipping_phone');
            $table->string('shipping_address')->nullable()->after('shipping_email');
            $table->string('shipping_city')->nullable()->after('shipping_address');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_country')->nullable()->after('shipping_state');
            $table->string('shipping_zip')->nullable()->after('shipping_country');
            $table->string('payment_mode')->nullable()->after('shipping_zip');
            if (!Schema::hasColumn('sales', 'sale_type')) {
                $table->string('sale_type')->nullable()->before('sale_note');
            }
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {

        });
    }
}
