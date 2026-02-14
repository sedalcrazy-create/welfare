#!/bin/bash

SERVER="root@37.152.174.87"
PROJECT_PATH="/var/www/welfare"

echo "=================================================="
echo "🚀 Deploy سیستم مهمانان به سرور"
echo "=================================================="
echo ""

echo "📡 اتصال به سرور: $SERVER"
echo ""

ssh $SERVER << 'ENDSSH'
    set -e

    cd /var/www/welfare

    echo "📦 دریافت آخرین تغییرات از GitHub..."
    git pull origin main

    echo ""
    echo "🐳 بررسی وضعیت Docker..."
    docker compose ps

    echo ""
    echo "⏸️  توقف کانتینرها..."
    docker compose down

    echo ""
    echo "🔨 Build و راه‌اندازی کانتینرها..."
    docker compose up -d --build

    echo ""
    echo "⏳ منتظر آماده شدن دیتابیس (10 ثانیه)..."
    sleep 10

    echo ""
    echo "🔄 اجرای migrations جدید..."
    docker compose exec -T app php artisan migrate --force

    echo ""
    echo "🧹 پاک کردن cache..."
    docker compose exec -T app php artisan cache:clear
    docker compose exec -T app php artisan config:clear
    docker compose exec -T app php artisan view:clear
    docker compose exec -T app php artisan route:clear

    echo ""
    echo "📊 بررسی migrations..."
    docker compose exec -T app php artisan migrate:status

    echo ""
    echo "✅ بررسی وضعیت نهایی..."
    docker compose ps

    echo ""
    echo "=================================================="
    echo "✅ Deploy با موفقیت انجام شد!"
    echo "=================================================="
    echo ""
    echo "🌐 دسترسی:"
    echo "  - URL: http://37.152.174.87:8083"
    echo "  - Admin: admin@bankmelli.ir / password"
    echo ""
    echo "📝 لاگ‌ها:"
    echo "  docker compose logs -f app"
    echo ""
    echo "=================================================="
ENDSSH

if [ $? -eq 0 ]; then
    echo ""
    echo "🎉 همه مراحل با موفقیت انجام شد!"
else
    echo ""
    echo "❌ خطا در deploy! لطفاً لاگ‌ها را بررسی کنید."
    exit 1
fi
