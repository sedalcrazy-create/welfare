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
        Schema::table('personnel', function (Blueprint $table) {
            $table->foreignId('preferred_period_id')
                ->nullable()
                ->after('preferred_center_id')
                ->constrained('periods')
                ->nullOnDelete()
                ->comment('دوره مورد نظر برای اقامت');

            // Indexes for performance
            $table->index('preferred_period_id');
            $table->index(['preferred_center_id', 'preferred_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->dropForeign(['preferred_period_id']);
            $table->dropIndex(['preferred_center_id', 'preferred_period_id']);
            $table->dropIndex(['preferred_period_id']);
            $table->dropColumn('preferred_period_id');
        });
    }
};
