#!/bin/bash

###############################################################################
# اسکریپت Deploy خودکار - سامانه رفاهی بانک ملی
# تاریخ: 1404/10/30
###############################################################################

set -e  # خروج در صورت خطا

# رنگ‌ها برای خروجی
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# توابع کمکی
info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# بررسی root
if [ "$EUID" -ne 0 ]; then
    error "این اسکریپت باید با دسترسی root اجرا شود"
    exit 1
fi

info "شروع فرآیند Deploy..."

# مسیر پروژه
PROJECT_PATH="/var/www/welfare"

# بررسی وجود Docker
if ! command -v docker &> /dev/null; then
    error "Docker نصب نیست!"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    error "Docker Compose نصب نیست!"
    exit 1
fi

success "Docker و Docker Compose نصب هستند"

# بررسی وجود Git
if ! command -v git &> /dev/null; then
    error "Git نصب نیست!"
    exit 1
fi

success "Git نصب است"

# رفتن به مسیر پروژه
if [ ! -d "$PROJECT_PATH" ]; then
    info "پوشه پروژه وجود ندارد. در حال Clone..."
    cd /var/www
    git clone https://github.com/sedalcrazy-create/welfare.git
    cd welfare
    success "پروژه با موفقیت Clone شد"
else
    info "رفتن به مسیر پروژه..."
    cd $PROJECT_PATH
    success "در مسیر پروژه هستیم"
fi

# دریافت آخرین تغییرات
info "دریافت آخرین تغییرات از GitHub..."
git fetch origin
git pull origin main
success "کد به‌روزرسانی شد"

# بررسی فایل .env
if [ ! -f ".env" ]; then
    warning "فایل .env وجود ندارد. کپی از .env.example..."
    cp .env.example .env
    warning "لطفاً فایل .env را ویرایش کنید!"
    nano .env
fi

# بیلد و اجرای Docker
info "بیلد و اجرای Docker containers..."
docker-compose up -d --build

# صبر برای آماده شدن سرویس‌ها
info "صبر برای آماده شدن سرویس‌ها..."
sleep 10

# نصب Dependencies
info "نصب Composer dependencies..."
docker-compose exec -T app composer install --no-dev --optimize-autoloader

# تولید App Key (اگر نیاز باشه)
if ! grep -q "APP_KEY=base64:" .env; then
    info "تولید App Key..."
    docker-compose exec -T app php artisan key:generate --force
fi

# دسترسی‌های فایل
info "تنظیم دسترسی‌های فایل..."
docker-compose exec -T app chmod -R 775 storage bootstrap/cache
docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache

# اجرای Migrations
info "اجرای Database Migrations..."
docker-compose exec -T app php artisan migrate --force

# پاک‌سازی Cache
info "پاک‌سازی Cache..."
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan view:clear
docker-compose exec -T app php artisan route:clear

# ری‌استارت سرویس‌ها
info "ری‌استارت سرویس‌ها..."
docker-compose restart app queue

# مشاهده وضعیت
info "وضعیت سرویس‌ها:"
docker-compose ps

# تست سلامت
info "تست سلامت API..."
sleep 3
HEALTH_CHECK=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8083/api/status)

if [ "$HEALTH_CHECK" = "200" ]; then
    success "API سالم است! ✓"
else
    warning "API پاسخ نمی‌دهد. کد: $HEALTH_CHECK"
fi

# نمایش اطلاعات
echo ""
echo "═══════════════════════════════════════════════════════════════════"
success "Deploy با موفقیت انجام شد!"
echo "═══════════════════════════════════════════════════════════════════"
echo ""
info "دسترسی به سایت:"
echo "  • پنل مدیریت: http://37.152.174.87:8083"
echo "  • API Status: http://37.152.174.87:8083/api/status"
echo ""
info "لاگین پیش‌فرض:"
echo "  • Email: admin@bankmelli.ir"
echo "  • Password: password"
echo ""
warning "یادتان نباشد رمز پیش‌فرض را تغییر دهید!"
echo ""
info "دستورات کاربردی:"
echo "  • مشاهده لاگ: docker-compose logs -f app"
echo "  • ری‌استارت: docker-compose restart"
echo "  • Tinker: docker-compose exec app php artisan tinker"
echo ""
echo "═══════════════════════════════════════════════════════════════════"

exit 0
