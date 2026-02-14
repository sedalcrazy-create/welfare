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
        Schema::create('personnel_personnel_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')
                ->constrained('personnel')
                ->onDelete('cascade')
                ->comment('پرسنل میزبان');
            $table->foreignId('guest_personnel_id')
                ->constrained('personnel')
                ->onDelete('cascade')
                ->comment('پرسنل مهمان');
            $table->string('relation')
                ->comment('نسبت: همسر، فرزند، پدر، مادر، ...');
            $table->text('notes')->nullable()->comment('یادداشت');
            $table->timestamps();

            // یک پرسنل نمی‌تواند دوبار به عنوان مهمان یک پرسنل اضافه شود
            $table->unique(['personnel_id', 'guest_personnel_id'], 'unique_personnel_guest');

            // Index برای جستجوی سریع
            $table->index('guest_personnel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_personnel_guests');
    }
};
