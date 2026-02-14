#!/bin/bash

# اسکریپت ساخت کاربر جدید با سهمیه

# پارامترها
USER_NAME="${1:-مدیر استان تهران}"
USER_EMAIL="${2:-tehran@bankmelli.ir}"
USER_PASSWORD="${3:-password123}"
USER_ROLE="${4:-provincial_admin}"

echo "==================================================="
echo "🎯 ساخت کاربر جدید با سهمیه"
echo "==================================================="
echo ""
echo "نام: $USER_NAME"
echo "ایمیل: $USER_EMAIL"
echo "نقش: $USER_ROLE"
echo ""

ssh root@37.152.174.87 << ENDSSH
cd /var/www/welfare

docker exec welfare_app php artisan tinker --execute="
\$user = \App\Models\User::firstOrCreate(
    ['email' => '$USER_EMAIL'],
    [
        'name' => '$USER_NAME',
        'password' => \Hash::make('$USER_PASSWORD'),
        'is_active' => true
    ]
);

if (\$user->wasRecentlyCreated) {
    \$user->assignRole('$USER_ROLE');
    echo '✅ کاربر جدید ساخته شد';
} else {
    echo '⚠️  کاربر از قبل وجود داشت';
}

echo PHP_EOL;
echo 'ID: ' . \$user->id;
echo PHP_EOL;

// سهمیه دادن
\$centers = \App\Models\Center::all();
foreach (\$centers as \$center) {
    \App\Models\UserCenterQuota::updateOrCreate(
        ['user_id' => \$user->id, 'center_id' => \$center->id],
        ['total_quota' => 10, 'used_quota' => 0]
    );
    echo 'سهمیه ' . \$center->name . ': 10';
    echo PHP_EOL;
}

echo PHP_EOL;
echo '✅ همه چیز آماده است!';
echo PHP_EOL;
echo 'ورود: https://ria.jafamhis.ir/welfare/login';
echo PHP_EOL;
echo 'ایمیل: $USER_EMAIL';
echo PHP_EOL;
echo 'رمز: $USER_PASSWORD';
"

ENDSSH

echo ""
echo "==================================================="
echo "✅ کاربر با موفقیت ساخته شد!"
echo "==================================================="
