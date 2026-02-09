<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('tariff_type')->nullable();
            $table->integer('guest_count')->default(1);
            $table->decimal('total_amount', 12, 0)->default(0);
            $table->integer('year');
            $table->integer('jalali_year');
            $table->timestamps();

            $table->index(['personnel_id', 'center_id']);
            $table->index(['personnel_id', 'center_id', 'check_out_date']);
            $table->index(['center_id', 'jalali_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_histories');
    }
};
