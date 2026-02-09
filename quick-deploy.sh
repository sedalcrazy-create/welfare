#!/bin/bash

###############################################################################
# Quick Deploy - بدون Build، فقط با Images آماده
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

info() { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }

PROJECT_PATH="/var/www/welfare"

info "Quick Deploy شروع شد..."

cd $PROJECT_PATH

# Stop old containers
info "متوقف کردن containerهای قدیمی..."
docker compose down 2>/dev/null || true

# Use quick compose
info "استفاده از docker-compose.quick.yml..."
cp docker-compose.quick.yml docker-compose.yml

# Start services
info "شروع سرویس‌ها..."
docker compose up -d

# Wait for services
info "صبر برای آماده شدن سرویس‌ها..."
sleep 15

# Install composer dependencies
info "نصب dependencies..."
docker compose exec -T app sh -c "
    apk add --no-cache git curl unzip &&
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer &&
    composer install --no-dev --optimize-autoloader
" || info "Composer قبلا نصب است"

# Set permissions
info "تنظیم permissions..."
docker compose exec -T app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Generate key if needed
info "تولید App Key..."
docker compose exec -T app php artisan key:generate --force || true

# Run migrations
info "اجرای migrations..."
docker compose exec -T app php artisan migrate --force

# Clear cache
info "پاک‌سازی cache..."
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan cache:clear

# Check status
info "وضعیت سرویس‌ها:"
docker compose ps

success "✅ Deploy با موفقیت انجام شد!"
echo ""
info "دسترسی به سایت:"
echo "  http://37.152.174.87:8083"
echo ""
info "لاگین پیش‌فرض:"
echo "  Email: admin@bankmelli.ir"
echo "  Password: password"
