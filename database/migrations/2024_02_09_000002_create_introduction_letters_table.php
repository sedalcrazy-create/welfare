<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('introduction_letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_code', 30)->unique(); // MHD-1404-001
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('restrict');
            $table->foreignId('center_id')->constrained()->onDelete('restrict');
            $table->foreignId('issued_by_user_id')->constrained('users')->onDelete('restrict');

            // Letter details
            $table->integer('family_count')->default(1);
            $table->text('notes')->nullable();

            // Dates
            $table->string('valid_from', 10)->nullable(); // تاریخ شمسی
            $table->string('valid_until', 10)->nullable(); // تاریخ شمسی
            $table->timestamp('issued_at');
            $table->timestamp('used_at')->nullable();

            // Status
            $table->enum('status', ['active', 'used', 'cancelled', 'expired'])->default('active');
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('letter_code');
            $table->index('status');
            $table->index(['center_id', 'status']);
            $table->index('issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('introduction_letters');
    }
};
