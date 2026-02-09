<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Setting Sample Quotas...\n";
echo "---\n";

$admin = App\Models\User::where('email', 'admin@bankmelli.ir')->first();
$welfare = App\Models\User::where('email', 'welfare@bankmelli.ir')->first();

$mashhad = App\Models\Center::where('name', 'like', '%مشهد%')->first();
$babolsar = App\Models\Center::where('name', 'like', '%بابلسر%')->first();
$chadegan = App\Models\Center::where('name', 'like', '%چادگان%')->first();

// Set quotas for admin user
if ($admin && $mashhad && $babolsar && $chadegan) {
    App\Models\UserCenterQuota::updateOrCreate(
        ['user_id' => $admin->id, 'center_id' => $mashhad->id],
        ['quota_total' => 2, 'quota_used' => 0]
    );

    App\Models\UserCenterQuota::updateOrCreate(
        ['user_id' => $admin->id, 'center_id' => $babolsar->id],
        ['quota_total' => 3, 'quota_used' => 0]
    );

    App\Models\UserCenterQuota::updateOrCreate(
        ['user_id' => $admin->id, 'center_id' => $chadegan->id],
        ['quota_total' => 2, 'quota_used' => 0]
    );

    echo "✓ Admin quotas set: مشهد=2, بابلسر=3, چادگان=2\n";
}

// Set quotas for welfare user
if ($welfare && $mashhad && $babolsar && $chadegan) {
    App\Models\UserCenterQuota::updateOrCreate(
        ['user_id' => $welfare->id, 'center_id' => $mashhad->id],
        ['quota_total' => 5, 'quota_used' => 0]
    );

    App\Models\UserCenterQuota::updateOrCreate(
        ['user_id' => $welfare->id, 'center_id' => $babolsar->id],
        ['quota_total' => 5, 'quota_used' => 0]
    );

    App\Models\UserCenterQuota::updateOrCreate(
        ['user_id' => $welfare->id, 'center_id' => $chadegan->id],
        ['quota_total' => 5, 'quota_used' => 0]
    );

    echo "✓ Welfare quotas set: مشهد=5, بابلسر=5, چادگان=5\n";
}

echo "\n✅ Sample quotas configured!\n";
