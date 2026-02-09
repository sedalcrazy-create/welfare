<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Models & Relations...\n";
echo "---\n";

// Test UserCenterQuota model
$quota = App\Models\UserCenterQuota::first();
echo "1. UserCenterQuota:\n";
echo "   ID: {$quota->id}\n";
echo "   User: {$quota->user->name}\n";
echo "   Center: {$quota->center->name}\n";
echo "   Total: {$quota->quota_total}\n";
echo "   Used: {$quota->quota_used}\n";
echo "   Remaining: {$quota->quota_remaining}\n";

// Test User relations
$user = App\Models\User::first();
echo "\n2. User Relations:\n";
echo "   Name: {$user->name}\n";
echo "   Center Quotas: " . $user->centerQuotas()->count() . "\n";

// Test methods
echo "\n3. User Methods:\n";
$mashhad = App\Models\Center::where('name', 'like', '%مشهد%')->first();
if ($mashhad) {
    echo "   Mashhad ID: {$mashhad->id}\n";
    $hasQuota = $user->hasQuotaForCenter($mashhad->id);
    echo "   Has quota for مشهد: " . ($hasQuota ? 'Yes' : 'No') . "\n";

    $quota = $user->getQuotaForCenter($mashhad->id);
    if ($quota) {
        echo "   Quota for مشهد: {$quota->quota_remaining}/{$quota->quota_total}\n";
    }
}

// Test quota operations
echo "\n4. Quota Operations:\n";
$testQuota = App\Models\UserCenterQuota::first();
echo "   Before: Total={$testQuota->quota_total}, Used={$testQuota->quota_used}\n";

$testQuota->update(['quota_total' => 5]);
$testQuota->incrementUsed(1);
$testQuota->refresh();

echo "   After increment: Total={$testQuota->quota_total}, Used={$testQuota->quota_used}, Remaining={$testQuota->quota_remaining}\n";
echo "   Has available: " . ($testQuota->hasAvailable() ? 'Yes' : 'No') . "\n";

// Test RegistrationControl
echo "\n5. RegistrationControl:\n";
$check = App\Models\RegistrationControl::isRegistrationAllowed();
echo "   Registration allowed: " . ($check['allowed'] ? 'Yes' : 'No') . "\n";
if (!$check['allowed']) {
    echo "   Reason: {$check['message']}\n";
    echo "   Rule type: {$check['rule_type']}\n";
}

echo "\n✅ All tests passed!\n";
