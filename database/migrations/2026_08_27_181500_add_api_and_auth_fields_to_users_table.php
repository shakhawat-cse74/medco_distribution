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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'api_token')) {
                $table->string('api_token', 500)->nullable()->default(null)->after('password');
            }
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'otp')) {
                $table->string('otp', 10)->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'otp_created_at')) {
                $table->timestamp('otp_created_at')->nullable()->after('otp');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar', 255)->nullable()->after('otp_created_at');
            }
            if (!Schema::hasColumn('users', 'device_id')) {
                $table->string('device_id', 255)->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'is_guest')) {
                $table->boolean('is_guest')->default(0)->after('device_id');
            }
            if (!Schema::hasColumn('users', 'fcm_token')) {
                $table->text('fcm_token')->nullable()->after('is_guest');
            }
            if (!Schema::hasColumn('users', 'apple_id')) {
                $table->string('apple_id', 255)->nullable()->after('fcm_token');
            }
            if (!Schema::hasColumn('users', 'notification')) {
                $table->boolean('notification')->default(1)->after('apple_id');
            }
            if (!Schema::hasColumn('users', 'messages')) {
                $table->boolean('messages')->default(1)->after('notification');
            }
        });

        // Ensure nullable fields for phone, company_name, biller_id, warehouse_id
        try {
            DB::statement("ALTER TABLE users MODIFY phone VARCHAR(255) NULL");
            DB::statement("ALTER TABLE users MODIFY company_name VARCHAR(255) NULL");
            DB::statement("ALTER TABLE users MODIFY role_id INT NULL DEFAULT 5");
            DB::statement("ALTER TABLE users MODIFY biller_id INT NULL");
            DB::statement("ALTER TABLE users MODIFY warehouse_id INT NULL");
            DB::statement("ALTER TABLE users MODIFY is_active TINYINT(1) NOT NULL DEFAULT 1");
            DB::statement("ALTER TABLE users MODIFY is_deleted TINYINT(1) NOT NULL DEFAULT 0");
        } catch (\Throwable $e) {
            // Ignore if already modified or constraint exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'api_token',
                'otp',
                'otp_created_at',
                'avatar',
                'device_id',
                'is_guest',
                'fcm_token',
                'apple_id',
                'notification',
                'messages'
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
