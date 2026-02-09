<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->string('number', 20);
            $table->string('name')->nullable();
            $table->enum('type', ['room', 'suite', 'villa', 'apartment'])->default('room');
            $table->integer('bed_count');
            $table->integer('floor')->nullable();
            $table->string('block', 10)->nullable();
            $table->json('amenities')->nullable();
            $table->boolean('is_management')->default(false);
            $table->enum('status', ['active', 'maintenance', 'blocked'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['center_id', 'number']);
            $table->index(['center_id', 'status']);
            $table->index(['center_id', 'bed_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
