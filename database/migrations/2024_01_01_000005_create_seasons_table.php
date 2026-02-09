<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['golden_peak', 'peak', 'mid_season', 'off_peak', 'super_off_peak']);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('jalali_start_date', 10)->nullable();
            $table->string('jalali_end_date', 10)->nullable();
            $table->integer('discount_rate')->default(0); // درصد تخفیف
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['center_id', 'type']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
