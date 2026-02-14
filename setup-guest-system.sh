#!/bin/bash

echo "=================================================="
echo "🚀 راه‌اندازی سیستم مدیریت مهمانان"
echo "=================================================="
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker در حال اجرا نیست!"
    echo "لطفاً ابتدا Docker Desktop را اجرا کنید."
    exit 1
fi

echo "✅ Docker در حال اجرا است"
echo ""

# Start containers if not running
echo "📦 بررسی و راه‌اندازی کانتینرها..."
cd "$(dirname "$0")"
docker-compose up -d

echo ""
echo "⏳ منتظر آماده شدن دیتابیس..."
sleep 5

echo ""
echo "🔄 اجرای migrations..."
docker-compose exec -T app php artisan migrate --force

if [ $? -eq 0 ]; then
    echo ""
    echo "=================================================="
    echo "✅ نصب با موفقیت انجام شد!"
    echo "=================================================="
    echo ""
    echo "📊 جداول ایجاد شده:"
    echo "  - guests (مهمانان)"
    echo "  - personnel_guests (رابطه many-to-many)"
    echo "  - lottery_entries.selected_guest_ids (فیلد جدید)"
    echo ""
    echo "🌐 دسترسی به پنل:"
    echo "  - URL: http://localhost:8080"
    echo "  - ایمیل: admin@bankmelli.ir"
    echo "  - رمز: password"
    echo ""
    echo "📖 راهنما: GUEST_SYSTEM_GUIDE.md"
    echo "=================================================="
else
    echo ""
    echo "❌ خطا در اجرای migrations"
    echo "لطفاً خطاها را بررسی کنید."
    exit 1
fi
