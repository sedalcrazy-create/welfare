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
        Schema::table('lottery_entries', function (Blueprint $table) {
            // آرایه‌ای از IDهای مهمانانی که برای این سفر انتخاب شده‌اند
            $table->json('selected_guest_ids')->nullable()->after('guests')->comment('آرایه IDهای مهمانان انتخاب شده: [1,2,3]');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lottery_entries', function (Blueprint $table) {
            $table->dropColumn('selected_guest_ids');
        });
    }
};
