<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('introduction_letters', function (Blueprint $table) {
            if (!Schema::hasColumn('introduction_letters', 'assigned_user_id')) {
                $table->foreignId('assigned_user_id')
                    ->nullable()
                    ->after('issued_by_user_id')
                    ->comment('یوزری که از سهمیه‌اش کم شده')
                    ->constrained('users')
                    ->onDelete('set null');

                $table->index('assigned_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('introduction_letters', function (Blueprint $table) {
            if (Schema::hasColumn('introduction_letters', 'assigned_user_id')) {
                $table->dropForeign(['assigned_user_id']);
                $table->dropIndex(['assigned_user_id']);
                $table->dropColumn('assigned_user_id');
            }
        });
    }
};
