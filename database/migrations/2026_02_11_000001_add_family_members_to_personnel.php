<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // اضافه کردن فیلد JSON برای اطلاعات همراهان
        Schema::table('personnel', function (Blueprint $table) {
            $table->json('family_members')
                ->nullable()
                ->after('family_count')
                ->comment('اطلاعات جزئی همراهان (نام، نسبت، کد ملی، تاریخ تولد)');
        });

        // برای رکوردهای موجود که employee_code ندارند، یک مقدار پیش‌فرض تولید می‌کنیم
        DB::statement("UPDATE personnel SET employee_code = CONCAT('TEMP-', id) WHERE employee_code IS NULL OR employee_code = ''");

        // حالا employee_code را اجباری می‌کنیم
        Schema::table('personnel', function (Blueprint $table) {
            $table->string('employee_code', 20)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            // حذف فیلد family_members
            $table->dropColumn('family_members');

            // employee_code را دوباره nullable می‌کنیم
            $table->string('employee_code', 20)->nullable()->change();
        });
    }
};
