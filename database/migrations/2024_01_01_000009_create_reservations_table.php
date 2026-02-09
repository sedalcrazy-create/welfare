<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('restrict');
            $table->foreignId('unit_id')->constrained()->onDelete('restrict');
            $table->foreignId('period_id')->constrained()->onDelete('restrict');
            $table->string('reservation_code', 20)->unique();
            $table->integer('guest_count')->default(1);
            $table->json('guests')->nullable();
            $table->enum('tariff_type', ['bank_rate', 'free_bank_rate', 'free_non_bank_rate'])->default('bank_rate');
            $table->decimal('accommodation_amount', 12, 0)->default(0);
            $table->decimal('meal_amount', 12, 0)->default(0);
            $table->decimal('discount_amount', 12, 0)->default(0);
            $table->decimal('total_amount', 12, 0)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('pending');
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['period_id', 'status']);
            $table->index(['personnel_id', 'status']);
            $table->index(['unit_id', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
