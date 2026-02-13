#!/bin/bash

###############################################################################
# اسکریپت Deploy بات بله - سامانه رفاهی بانک ملی
# تاریخ: 1404/11/25
# توضیحات: این اسکریپت بات بله را کانفیگ و deploy می‌کند
###############################################################################

set -e  # خروج در صورت خطا

# رنگ‌ها برای خروجی
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
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

step() {
    echo -e "${CYAN}==>${NC} $1"
}

# بررسی root
if [ "$EUID" -ne 0 ]; then
    error "این اسکریپت باید با دسترسی root اجرا شود"
    exit 1
fi

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║          Deploy بات بله - سامانه رفاهی بانک ملی              ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# مسیر پروژه
PROJECT_PATH="/var/www/welfare"

# رفتن به مسیر پروژه
if [ ! -d "$PROJECT_PATH" ]; then
    error "پوشه پروژه ($PROJECT_PATH) وجود ندارد!"
    exit 1
fi

cd $PROJECT_PATH
success "در مسیر پروژه هستیم: $PROJECT_PATH"
echo ""

# ════════════════════════════════════════════════════════════════
step "مرحله 1: دریافت آخرین کد از GitHub"
# ════════════════════════════════════════════════════════════════

info "در حال دریافت آخرین تغییرات..."
git fetch origin
git pull origin main
success "کد به‌روزرسانی شد"
echo ""

# ════════════════════════════════════════════════════════════════
step "مرحله 2: بررسی تنظیمات Bale Bot در .env"
# ════════════════════════════════════════════════════════════════

if [ ! -f ".env" ]; then
    error "فایل .env وجود ندارد!"
    exit 1
fi

# بررسی وجود BALE_BOT_TOKEN
if ! grep -q "BALE_BOT_TOKEN" .env; then
    warning "BALE_BOT_TOKEN در .env یافت نشد. در حال اضافه کردن..."

    echo "" >> .env
    echo "# Bale Bot Configuration" >> .env
    echo "BALE_BOT_TOKEN=1159941038:QJVEuVhVJOZCtQfy4n38uMdTGDMzastM_WE" >> .env
    echo "BALE_BOT_USERNAME=welfarebot" >> .env
    echo "BALE_API_BASE_URL=https://tapi.bale.ai/bot" >> .env
    echo "BALE_WEBHOOK_URL=https://ria.jafamhis.ir/welfare/api/bale/webhook" >> .env

    success "تنظیمات Bale Bot به .env اضافه شد"
else
    success "تنظیمات Bale Bot در .env موجود است"
fi

# نمایش تنظیمات Bale Bot
info "تنظیمات Bale Bot:"
grep "BALE_" .env | while read line; do
    # پنهان کردن token کامل
    if [[ $line == BALE_BOT_TOKEN* ]]; then
        echo "  BALE_BOT_TOKEN=***...***"
    else
        echo "  $line"
    fi
done
echo ""

# ════════════════════════════════════════════════════════════════
step "مرحله 3: Build و ری‌استارت Docker Containers"
# ════════════════════════════════════════════════════════════════

info "در حال build و ری‌استارت containers..."
docker-compose up -d --build

info "صبر برای آماده شدن سرویس‌ها..."
sleep 10

success "Containers آماده هستند"
echo ""

# ════════════════════════════════════════════════════════════════
step "مرحله 4: نصب Dependencies"
# ════════════════════════════════════════════════════════════════

info "نصب Composer dependencies..."
docker-compose exec -T app composer install --no-dev --optimize-autoloader
success "Dependencies نصب شدند"
echo ""

# ════════════════════════════════════════════════════════════════
step "مرحله 5: پاک‌سازی Cache"
# ════════════════════════════════════════════════════════════════

info "پاک‌سازی cache..."
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan event:clear
docker-compose exec -T app php artisan view:clear
success "Cache پاک شد"
echo ""

# ════════════════════════════════════════════════════════════════
step "مرحله 6: ری‌استارت Queue Worker"
# ════════════════════════════════════════════════════════════════

info "ری‌استارت queue worker برای Events..."
docker-compose restart queue
sleep 3
success "Queue worker ری‌استارت شد"
echo ""

# ════════════════════════════════════════════════════════════════
step "مرحله 7: Setup Webhook"
# ════════════════════════════════════════════════════════════════

info "در حال setup webhook..."
docker-compose exec -T app php artisan bale:setup-webhook

if [ $? -eq 0 ]; then
    success "Webhook با موفقیت setup شد"
else
    error "خطا در setup webhook!"
    warning "لطفاً به صورت دستی تنظیم کنید:"
    warning "docker-compose exec app php artisan bale:setup-webhook"
fi
echo ""

# ════════════════════════════════════════════════════════════════
step "مرحله 8: بررسی Webhook"
# ════════════════════════════════════════════════════════════════

info "دریافت اطلاعات webhook..."
docker-compose exec -T app php artisan bale:setup-webhook --info
echo ""

# ════════════════════════════════════════════════════════════════
step "مرحله 9: تست‌های اولیه"
# ════════════════════════════════════════════════════════════════

# تست Redis
info "تست Redis..."
REDIS_TEST=$(docker-compose exec -T app php artisan tinker --execute="echo Redis::ping();")
if [[ $REDIS_TEST == *"PONG"* ]]; then
    success "Redis کار می‌کند ✓"
else
    error "Redis پاسخ نمی‌دهد ✗"
fi

# تست Database
info "تست Database..."
DB_TEST=$(docker-compose exec -T app php artisan tinker --execute="echo DB::connection()->getPdo() ? 'OK' : 'FAIL';")
if [[ $DB_TEST == *"OK"* ]]; then
    success "Database متصل است ✓"
else
    error "Database متصل نیست ✗"
fi

# وضعیت Containers
info "وضعیت Containers:"
docker-compose ps
echo ""

# ════════════════════════════════════════════════════════════════
# نمایش اطلاعات نهایی
# ════════════════════════════════════════════════════════════════

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                  ✅ Deploy با موفقیت انجام شد!                ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

info "🤖 اطلاعات بات:"
echo "  • نام کاربری: @welfarebot"
echo "  • لینک مستقیم: https://ble.ir/welfarebot"
echo "  • لینک وب: https://web.bale.ai/welfarebot"
echo ""

info "🔗 Webhook:"
echo "  • URL: https://ria.jafamhis.ir/welfare/api/bale/webhook/***"
echo "  • وضعیت: فعال ✓"
echo ""

info "📱 تست بات:"
echo "  1. در بله جستجو کنید: @welfarebot"
echo "  2. دکمه Start را بزنید"
echo "  3. دستور /start ارسال کنید"
echo "  4. باید پیام خوش‌آمدگویی دریافت کنید"
echo ""

info "🔍 دستورات کاربردی:"
echo "  • مشاهده لاگ بات:"
echo "    docker-compose exec app tail -f storage/logs/bale-bot.log"
echo ""
echo "  • مشاهده لاگ queue:"
echo "    docker-compose logs -f queue"
echo ""
echo "  • بررسی webhook:"
echo "    docker-compose exec app php artisan bale:setup-webhook --info"
echo ""
echo "  • تست Redis:"
echo "    docker-compose exec app php artisan tinker"
echo "    >>> Redis::ping();"
echo ""

warning "⚠️  یادآوری:"
echo "  • برای تست کامل، یک درخواست در بات ثبت کنید"
echo "  • از پنل ادمین تأیید/رد کنید"
echo "  • بررسی کنید notification ارسال شده"
echo ""

info "📚 مستندات:"
echo "  • راهنمای کاربری: BALE_BOT_USER_GUIDE.md"
echo "  • راهنمای سریع: BALE_BOT_QUICK_GUIDE.md"
echo "  • راهنمای Deploy: BALE_BOT_DEPLOYMENT_TESTING.md"
echo ""

echo "════════════════════════════════════════════════════════════════"
success "همه چیز آماده است! بات بله فعال شد 🎉"
echo "════════════════════════════════════════════════════════════════"
echo ""

exit 0
