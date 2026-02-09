#!/bin/bash

###############################################################################
# اسکریپت به‌روزرسانی - سامانه رفاهی بانک ملی
# برای دریافت آخرین تغییرات از GitHub و اعمال آن‌ها
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }

PROJECT_PATH="/var/www/welfare"

if [ ! -d "$PROJECT_PATH" ]; then
    error "پروژه در مسیر $PROJECT_PATH یافت نشد!"
    exit 1
fi

cd $PROJECT_PATH

info "شروع به‌روزرسانی..."

# دریافت تغییرات
info "دریافت تغییرات از GitHub..."
git fetch origin
git pull origin main

# نصب dependencies جدید
info "بررسی dependencies جدید..."
docker-compose exec -T app composer install --no-dev --optimize-autoloader

# اجرای migrations جدید
info "اجرای migrations جدید..."
docker-compose exec -T app php artisan migrate --force

# پاک‌سازی cache
info "پاک‌سازی cache..."
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan view:clear

# ری‌استارت
info "ری‌استارت سرویس‌ها..."
docker-compose restart app queue

success "به‌روزرسانی با موفقیت انجام شد!"

# نمایش آخرین commit
echo ""
info "آخرین تغییرات:"
git log --oneline -n 3

exit 0
