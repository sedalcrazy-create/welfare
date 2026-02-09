<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_controls', function (Blueprint $table) {
            $table->id();
            $table->enum('rule_type', ['global', 'date_range', 'center', 'period'])->index();
            $table->boolean('is_active')->default(true)->index();

            // Date range control
            $table->string('start_date', 10)->nullable()->comment('YYYY-MM-DD Jalali');
            $table->string('end_date', 10)->nullable()->comment('YYYY-MM-DD Jalali');

            // Center/Period control
            $table->foreignId('center_id')->nullable()->constrained('centers')->onDelete('cascade');
            $table->foreignId('period_id')->nullable()->constrained('periods')->onDelete('cascade');

            // Settings
            $table->boolean('allow_registration')->default(true);
            $table->text('message')->nullable()->comment('پیام نمایش داده شده به کاربر');

            // Metadata
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
            $table->index('center_id');
            $table->index('period_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_controls');
    }
};
