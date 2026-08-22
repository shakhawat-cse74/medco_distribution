<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('languages') && !Schema::hasColumn('languages', 'code')) {
            Schema::table('languages', function (Blueprint $table) {
                $table->string('code')->nullable()->after('language');
            });

            DB::statement('UPDATE languages SET code = language WHERE code IS NULL OR code = ""');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('languages') && Schema::hasColumn('languages', 'code')) {
            Schema::table('languages', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};
