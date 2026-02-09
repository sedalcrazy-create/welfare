<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\User;
use App\Models\UserCenterQuota;
use Illuminate\Database\Seeder;

class InitializeUserCenterQuotasSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $centers = Center::all();

        $count = 0;

        foreach ($users as $user) {
            foreach ($centers as $center) {
                UserCenterQuota::firstOrCreate([
                    'user_id' => $user->id,
                    'center_id' => $center->id,
                ], [
                    'quota_total' => 0,
                    'quota_used' => 0,
                ]);
                $count++;
            }
        }

        $this->command->info("Created {$count} quota records for {$users->count()} users and {$centers->count()} centers");
    }
}
