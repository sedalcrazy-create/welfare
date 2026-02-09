<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->onDelete('restrict');
            $table->string('employee_code', 20)->unique();
            $table->string('national_code', 10)->unique();
            $table->string('full_name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('father_name')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('birth_date', 10)->nullable(); // تاریخ شمسی
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('bale_user_id')->nullable()->unique();
            $table->enum('employment_status', ['active', 'retired'])->default('active');
            $table->string('service_location')->nullable();
            $table->string('department')->nullable();
            $table->integer('service_years')->default(0);
            $table->string('hire_date', 10)->nullable(); // تاریخ شمسی
            $table->integer('family_count')->default(1);
            $table->boolean('is_isargar')->default(false);
            $table->string('isargar_type')->nullable();
            $table->integer('isargar_percentage')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['province_id', 'employment_status']);
            $table->index('is_isargar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel');
    }
};
