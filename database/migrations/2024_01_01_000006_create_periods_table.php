<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->foreignId('season_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('jalali_start_date', 10)->nullable();
            $table->string('jalali_end_date', 10)->nullable();
            $table->integer('capacity'); // تعداد واحد قابل رزرو
            $table->integer('reserved_count')->default(0);
            $table->enum('status', ['draft', 'open', 'closed', 'completed'])->default('draft');
            $table->timestamps();

            $table->index(['center_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
