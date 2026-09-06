```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'route_id')) {
                $table->unsignedBigInteger('route_id')->nullable()->after('delivery_man_id');
            }

            if (!Schema::hasColumn('sales', 'installment_parent_id')) {
                $table->unsignedBigInteger('installment_parent_id')->nullable()->after('route_id');
            }

            if (!Schema::hasColumn('sales', 'installment_amount')) {
                $table->decimal('installment_amount', 15, 2)->nullable()->after('installment_parent_id');
            }

            if (!Schema::hasColumn('sales', 'installment_months')) {
                $table->integer('installment_months')->nullable()->after('installment_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $cols = [
                'installment_parent_id',
                'installment_amount',
                'installment_months'
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('sales', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
