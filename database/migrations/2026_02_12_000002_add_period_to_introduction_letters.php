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
        Schema::table('introduction_letters', function (Blueprint $table) {
            $table->foreignId('period_id')
                ->nullable()
                ->after('center_id')
                ->constrained('periods')
                ->restrictOnDelete()
                ->comment('دوره اقامت');

            // Indexes for performance
            $table->index(['center_id', 'period_id']);
            $table->index(['period_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('introduction_letters', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropIndex(['center_id', 'period_id']);
            $table->dropIndex(['period_id', 'status']);
            $table->dropColumn('period_id');
        });
    }
};
