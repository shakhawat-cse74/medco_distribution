<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('delivery_man_id')->nullable();
            $table->string('type')->nullable();
            $table->string('title');
            $table->text('body');
            $table->string('related_type')->nullable();
            $table->integer('related_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('channel')->default('push');
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_notifications');
    }
};
