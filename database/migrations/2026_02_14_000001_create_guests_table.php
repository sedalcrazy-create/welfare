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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('national_code', 10)->unique()->comment('کد ملی مهمان - یکتا');
            $table->string('full_name')->comment('نام کامل');
            $table->string('relation')->comment('نسبت: همسر، فرزند، پدر، مادر، پدر همسر، مادر همسر، دوست، فامیل، سایر');
            $table->date('birth_date')->nullable()->comment('تاریخ تولد');
            $table->enum('gender', ['male', 'female'])->nullable()->comment('جنسیت');
            $table->string('phone', 20)->nullable()->comment('شماره تماس');
            $table->text('notes')->nullable()->comment('یادداشت');
            $table->timestamps();

            // Indexes
            $table->index('national_code');
            $table->index('relation');
        });

        Schema::create('personnel_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->text('notes')->nullable()->comment('یادداشت درباره این مهمان برای این پرسنل');
            $table->timestamps();

            // یک پرسنل نمی‌تواند یک مهمان را دوبار اضافه کند
            $table->unique(['personnel_id', 'guest_id']);

            // Indexes
            $table->index('personnel_id');
            $table->index('guest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_guests');
        Schema::dropIfExists('guests');
    }
};
