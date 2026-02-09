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
            $table->integer('quota_total')->default(0)->after('password');
            $table->integer('quota_used')->default(0)->after('quota_total');
            $table->integer('quota_remaining')->virtualAs('quota_total - quota_used')->after('quota_used');

            // Provincial assignment (if provincial admin)
            $table->foreignId('province_id')->nullable()->after('quota_remaining')->constrained()->onDelete('set null');

            $table->index('province_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'quota_total',
                'quota_used',
                'quota_remaining',
                'province_id'
            ]);
        });
    }
};
