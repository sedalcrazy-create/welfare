<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // دسترسی‌ها
        $permissions = [
            // مراکز
            'centers.view',
            'centers.create',
            'centers.edit',
            'centers.delete',

            // واحدها
            'units.view',
            'units.create',
            'units.edit',
            'units.delete',

            // پرسنل
            'personnel.view',
            'personnel.create',
            'personnel.edit',
            'personnel.delete',
            'personnel.import',

            // دوره‌ها
            'periods.view',
            'periods.create',
            'periods.edit',
            'periods.delete',

            // قرعه‌کشی
            'lotteries.view',
            'lotteries.create',
            'lotteries.edit',
            'lotteries.draw',
            'lotteries.cancel',

            // شرکت‌کنندگان
            'entries.view',
            'entries.approve',
            'entries.reject',

            // رزرو
            'reservations.view',
            'reservations.create',
            'reservations.edit',
            'reservations.cancel',
            'reservations.check_in',
            'reservations.check_out',

            // گزارش‌ها
            'reports.view',
            'reports.occupancy',
            'reports.provincial',
            'reports.financial',
            'reports.fairness',

            // کاربران
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // تنظیمات
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // نقش‌ها
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'centers.view', 'centers.create', 'centers.edit',
            'units.view', 'units.create', 'units.edit',
            'personnel.view', 'personnel.create', 'personnel.edit', 'personnel.import',
            'periods.view', 'periods.create', 'periods.edit',
            'lotteries.view', 'lotteries.create', 'lotteries.edit', 'lotteries.draw',
            'entries.view', 'entries.approve', 'entries.reject',
            'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.cancel',
            'reservations.check_in', 'reservations.check_out',
            'reports.view', 'reports.occupancy', 'reports.provincial', 'reports.financial', 'reports.fairness',
            'users.view',
        ]);

        $provincialAdmin = Role::create(['name' => 'provincial_admin']);
        $provincialAdmin->givePermissionTo([
            'centers.view',
            'units.view',
            'personnel.view',
            'periods.view',
            'lotteries.view',
            'entries.view', 'entries.approve', 'entries.reject',
            'reservations.view',
            'reports.view', 'reports.provincial',
        ]);

        $operator = Role::create(['name' => 'operator']);
        $operator->givePermissionTo([
            'centers.view',
            'units.view',
            'personnel.view',
            'periods.view',
            'lotteries.view',
            'entries.view',
            'reservations.view', 'reservations.check_in', 'reservations.check_out',
        ]);

        Role::create(['name' => 'user']);
    }
}
