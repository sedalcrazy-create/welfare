<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_center_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('center_id')->constrained('centers')->onDelete('cascade');
            $table->integer('quota_total')->default(0)->comment('تعداد کل سهمیه برای این مرکز');
            $table->integer('quota_used')->default(0)->comment('تعداد استفاده شده');
            $table->timestamps();

            // Unique constraint: each user can have only one quota record per center
            $table->unique(['user_id', 'center_id']);
            $table->index(['user_id', 'center_id']);
        });

        // Add generated column for quota_remaining using raw SQL for PostgreSQL
        \DB::statement('ALTER TABLE user_center_quotas ADD COLUMN quota_remaining INTEGER GENERATED ALWAYS AS (quota_total - quota_used) STORED');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_center_quotas');
    }
};
