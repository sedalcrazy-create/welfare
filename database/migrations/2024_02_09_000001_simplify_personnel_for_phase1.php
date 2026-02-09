<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            // Make fields nullable or optional for phase 1
            $table->foreignId('province_id')->nullable()->change();
            $table->string('employee_code', 20)->nullable()->change();

            // Add new fields for phase 1
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_active');
            $table->enum('registration_source', ['manual', 'bale_bot', 'web'])->default('manual')->after('status');
            $table->foreignId('preferred_center_id')->nullable()->after('registration_source')->constrained('centers')->onDelete('set null');
            $table->text('notes')->nullable()->after('preferred_center_id');
            $table->string('tracking_code', 20)->nullable()->unique()->after('notes');

            // Add indexes
            $table->index('status');
            $table->index('registration_source');
            $table->index('tracking_code');
        });
    }

    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'registration_source',
                'preferred_center_id',
                'notes',
                'tracking_code'
            ]);

            $table->foreignId('province_id')->nullable(false)->change();
            $table->string('employee_code', 20)->unique()->nullable(false)->change();
        });
    }
};
