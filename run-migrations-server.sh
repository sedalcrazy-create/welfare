#!/bin/bash

echo "=================================================="
echo "🔄 اجرای Migrations روی سرور"
echo "=================================================="
echo ""

ssh root@37.152.174.87 << 'ENDSSH'
    cd /var/www/welfare

    echo "📊 بررسی وضعیت Docker..."
    docker compose ps

    echo ""
    echo "🔄 اجرای migrations..."
    docker compose exec -T app php artisan migrate --force

    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Migrations با موفقیت اجرا شد!"

        echo ""
        echo "📊 وضعیت migrations:"
        docker compose exec -T app php artisan migrate:status

        echo ""
        echo "🧹 پاک کردن cache..."
        docker compose exec -T app php artisan cache:clear
        docker compose exec -T app php artisan config:clear
        docker compose exec -T app php artisan view:clear

        echo ""
        echo "=================================================="
        echo "✅ تکمیل شد!"
        echo "=================================================="
        echo ""
        echo "🌐 تست کنید: http://37.152.174.87:8083"
        echo ""
    else
        echo ""
        echo "❌ خطا در اجرای migrations!"
        exit 1
    fi
ENDSSH
