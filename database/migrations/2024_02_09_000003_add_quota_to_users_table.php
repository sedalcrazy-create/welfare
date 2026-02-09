<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Quota management for admins/provincial admins
            if (!Schema::hasColumn('users', 'quota_total')) {
                $table->integer('quota_total')->default(0)->after('password');
            }
            if (!Schema::hasColumn('users', 'quota_used')) {
                $table->integer('quota_used')->default(0)->after('quota_total');
            }
        });

        // Add generated column using raw SQL for PostgreSQL
        if (!Schema::hasColumn('users', 'quota_remaining')) {
            \DB::statement('ALTER TABLE users ADD COLUMN quota_remaining INTEGER GENERATED ALWAYS AS (quota_total - quota_used) STORED');
        }

        // Add province_id if not exists (may already exist from previous migrations)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'province_id')) {
                $table->foreignId('province_id')->nullable()->after('quota_remaining')->constrained('provinces')->onDelete('set null');
                $table->index('province_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'quota_total')) {
                $table->dropColumn('quota_total');
            }
            if (Schema::hasColumn('users', 'quota_used')) {
                $table->dropColumn('quota_used');
            }
            if (Schema::hasColumn('users', 'quota_remaining')) {
                $table->dropColumn('quota_remaining');
            }
            // Don't drop province_id as it may be used by other features
        });
    }
};
