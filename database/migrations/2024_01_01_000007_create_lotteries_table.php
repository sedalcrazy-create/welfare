<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotteries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('registration_start_date');
            $table->dateTime('registration_end_date');
            $table->dateTime('draw_date');
            $table->enum('status', ['draft', 'open', 'closed', 'drawn', 'approval', 'completed', 'cancelled'])->default('draft');
            $table->string('algorithm')->default('weighted_random');
            $table->dateTime('drawn_at')->nullable();
            $table->foreignId('drawn_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('total_participants')->default(0);
            $table->integer('total_winners')->default(0);
            $table->timestamps();

            $table->index(['status', 'draw_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotteries');
    }
};
