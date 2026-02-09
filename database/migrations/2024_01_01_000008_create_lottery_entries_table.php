<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_id')->constrained()->onDelete('cascade');
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('province_id')->constrained()->onDelete('restrict');
            $table->integer('family_count')->default(1);
            $table->json('guests')->nullable(); // اطلاعات همراهان
            $table->json('preferred_unit_types')->nullable();
            $table->decimal('priority_score', 10, 2)->default(0);
            $table->integer('rank')->nullable();
            $table->enum('status', ['pending', 'won', 'lost', 'waitlist', 'approved', 'rejected', 'cancelled', 'expired'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['lottery_id', 'personnel_id']);
            $table->index(['lottery_id', 'status']);
            $table->index(['lottery_id', 'province_id']);
            $table->index(['lottery_id', 'priority_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_entries');
    }
};
