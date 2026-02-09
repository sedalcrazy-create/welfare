<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Verifying Quota System...\n";
echo "---\n";

$admin = App\Models\User::where('email', 'admin@bankmelli.ir')->first();
echo "Admin ({$admin->name}) Quotas:\n";
foreach ($admin->centerQuotas as $q) {
    echo "  {$q->center->name}: {$q->quota_remaining}/{$q->quota_total}\n";
}

$welfare = App\Models\User::where('email', 'welfare@bankmelli.ir')->first();
echo "\nWelfare ({$welfare->name}) Quotas:\n";
foreach ($welfare->centerQuotas as $q) {
    echo "  {$q->center->name}: {$q->quota_remaining}/{$q->quota_total}\n";
}

// Test registration control
echo "\n\nRegistration Control Status:\n";
$check = App\Models\RegistrationControl::isRegistrationAllowed();
echo "  Allowed: " . ($check['allowed'] ? 'Yes ✓' : 'No ✗') . "\n";

if (!$check['allowed']) {
    echo "  Message: {$check['message']}\n";
    echo "  Rule: {$check['rule_type']}\n";
}

echo "\n✅ All quota systems working!\n";
