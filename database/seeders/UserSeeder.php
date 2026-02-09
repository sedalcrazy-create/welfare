<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // مدیر سیستم
        $superAdmin = User::create([
            'name' => 'مدیر سیستم',
            'email' => 'admin@bankmelli.ir',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        // ادمین اداره کل
        $admin = User::create([
            'name' => 'کارشناس رفاه',
            'email' => 'welfare@bankmelli.ir',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // اپراتور
        $operator = User::create([
            'name' => 'اپراتور',
            'email' => 'operator@bankmelli.ir',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $operator->assignRole('operator');
    }
}
