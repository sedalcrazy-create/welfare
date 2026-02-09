# راهنمای نصب و Deploy - سامانه رفاهی بانک ملی

## تاریخ: ۲۰ بهمن ۱۴۰۴

---

## اطلاعات سرور

```
IP: 37.152.174.87
OS: Ubuntu 22.04.5 LTS
SSH Port: 22
Web Port: 8083
Project Path: /var/www/welfare
```

---

## مرحله 1: اتصال به سرور

### روش 1: با کلید SSH (توصیه شده)
```bash
ssh root@37.152.174.87
```

### روش 2: با SSH Config
فایل `~/.ssh/config`:
```
Host welfare-server
    HostName 37.152.174.87
    User root
    IdentityFile ~/.ssh/id_rsa
    Port 22
```

سپس:
```bash
ssh welfare-server
```

---

## مرحله 2: نصب پیش‌نیازها (اگر نصب نشده)

```bash
# بررسی Docker
docker --version
docker-compose --version

# اگر نصب نبود:
apt update
apt install -y docker.io docker-compose git

# فعال‌سازی Docker
systemctl enable docker
systemctl start docker
```

---

## مرحله 3: Clone پروژه از GitHub

```bash
# رفتن به مسیر مناسب
cd /var/www

# حذف فولدر قبلی (اگر وجود داره)
rm -rf welfare

# Clone از GitHub
git clone https://github.com/sedalcrazy-create/welfare.git

# رفتن به پوشه پروژه
cd welfare

# مشاهده آخرین تغییرات
git log --oneline -n 5
```

---

## مرحله 4: تنظیمات Environment

```bash
# کپی فایل .env نمونه
cp .env.example .env

# ویرایش فایل .env
nano .env
```

### تنظیمات مهم در `.env`:

```env
APP_NAME="سامانه رفاهی بانک ملی"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://37.152.174.87:8083

# Database
DB_CONNECTION=pgsql
DB_HOST=welfare_postgres
DB_PORT=5432
DB_DATABASE=welfare_system
DB_USERNAME=welfare_user
DB_PASSWORD=بگذارید_یک_رمز_امن

# Redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=welfare_redis
REDIS_PORT=6379

# اگر بات بله دارید
BALE_BOT_TOKEN=your_token_here
```

**نکته مهم:** برای production حتماً `APP_DEBUG=false` و رمزهای قوی بگذارید!

---

## مرحله 5: اجرای Docker

```bash
# بیلد و اجرای کانتینرها (در مسیر /var/www/welfare)
docker-compose up -d --build

# مشاهده وضعیت
docker-compose ps

# باید 5 سرویس running باشه:
# - welfare_app (PHP-FPM)
# - welfare_nginx (Web Server)
# - welfare_postgres (Database)
# - welfare_redis (Cache/Queue)
# - welfare_queue (Queue Worker)
```

---

## مرحله 6: نصب Dependencies و Setup

```bash
# نصب Composer dependencies
docker-compose exec app composer install --no-dev --optimize-autoloader

# تولید App Key
docker-compose exec app php artisan key:generate

# دسترسی‌های فایل
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache

# اجرای Migrations
docker-compose exec app php artisan migrate --force

# اجرای Seeders (برای اولین بار)
docker-compose exec app php artisan db:seed --force
```

---

## مرحله 7: تست سیستم

### در مرورگر:
```
http://37.152.174.87:8083
```

### لاگین پیش‌فرض:
```
Email: admin@bankmelli.ir
Password: password
```

### تست API:
```bash
curl http://37.152.174.87:8083/api/status
```

باید پاسخ بگیرید:
```json
{"status":"ok","message":"Welfare API is running"}
```

---

## مرحله 8: تخصیص سهمیه به ادمین

```bash
# ورود به Tinker
docker-compose exec app php artisan tinker
```

در Tinker:
```php
// پیدا کردن ادمین
$admin = User::where('email', 'admin@bankmelli.ir')->first();

// تخصیص سهمیه
$admin->quota_total = 100;
$admin->save();

// چک کردن
echo "سهمیه: " . $admin->quota_total;
exit
```

---

## مرحله 9: تنظیمات Nginx (اختیاری - برای دامنه)

اگر می‌خواهید دامنه اختصاصی داشته باشید (مثلاً `welfare.example.com`):

```bash
# ایجاد فایل nginx config
nano /etc/nginx/sites-available/welfare
```

محتوا:
```nginx
server {
    listen 80;
    server_name welfare.example.com;

    location / {
        proxy_pass http://localhost:8083;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

فعال‌سازی:
```bash
ln -s /etc/nginx/sites-available/welfare /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## دستورات کاربردی

### مشاهده لاگ‌ها:
```bash
# همه سرویس‌ها
docker-compose logs -f

# فقط app
docker-compose logs -f app

# فقط nginx
docker-compose logs -f nginx

# لاگ Laravel
docker-compose exec app tail -f storage/logs/laravel.log
```

### ری‌استارت سرویس‌ها:
```bash
# همه
docker-compose restart

# فقط app
docker-compose restart app

# فقط queue worker
docker-compose restart queue
```

### به‌روزرسانی کد:
```bash
cd /var/www/welfare

# دریافت آخرین تغییرات
git pull origin main

# نصب dependencies جدید (اگر هست)
docker-compose exec app composer install --no-dev

# اجرای migrations جدید
docker-compose exec app php artisan migrate --force

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear

# ری‌استارت
docker-compose restart app queue
```

### بکاپ دیتابیس:
```bash
# ایجاد بکاپ
docker-compose exec welfare_postgres pg_dump -U welfare_user welfare_system > backup_$(date +%Y%m%d_%H%M%S).sql

# ریستور بکاپ
docker-compose exec -T welfare_postgres psql -U welfare_user welfare_system < backup_file.sql
```

### مشاهده وضعیت سرویس‌ها:
```bash
# Docker services
docker-compose ps

# استفاده از منابع
docker stats

# دیسک
df -h

# رم
free -h
```

---

## تست API از بیرون سرور

### ثبت درخواست:
```bash
curl -X POST http://37.152.174.87:8083/api/bale/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "علی احمدی",
    "national_code": "1234567890",
    "phone": "09123456789",
    "family_count": 4,
    "preferred_center_id": 1
  }'
```

### چک وضعیت:
```bash
curl -X POST http://37.152.174.87:8083/api/bale/check-status \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "1234567890",
    "identifier_type": "national_code"
  }'
```

---

## عیب‌یابی

### مشکل: سایت باز نمیشه
```bash
# چک کردن nginx
docker-compose logs nginx

# چک کردن پورت
netstat -tlnp | grep 8083
```

### مشکل: خطای 500
```bash
# مشاهده لاگ Laravel
docker-compose exec app tail -50 storage/logs/laravel.log

# چک permissions
docker-compose exec app ls -la storage/
```

### مشکل: دیتابیس connect نمیشه
```bash
# چک postgres
docker-compose logs welfare_postgres

# تست اتصال
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### مشکل: Queue کار نمی‌کنه
```bash
# ری‌استارت queue worker
docker-compose restart queue

# لاگ queue
docker-compose logs -f queue
```

---

## امنیت

### Firewall (UFW):
```bash
# فعال‌سازی
ufw enable

# مجاز کردن پورت‌ها
ufw allow 22/tcp    # SSH
ufw allow 8083/tcp  # Web

# چک وضعیت
ufw status
```

### تغییر رمزهای پیش‌فرض:
```bash
docker-compose exec app php artisan tinker
```
```php
$admin = User::where('email', 'admin@bankmelli.ir')->first();
$admin->password = bcrypt('رمز_جدید_قوی');
$admin->save();
```

### بکاپ خودکار (Cron):
```bash
crontab -e
```
اضافه کنید:
```
0 2 * * * cd /var/www/welfare && docker-compose exec -T welfare_postgres pg_dump -U welfare_user welfare_system > /backup/welfare_$(date +\%Y\%m\%d).sql
```

---

## مانیتورینگ

### نصب Portainer (اختیاری - برای مدیریت گرافیکی Docker):
```bash
docker volume create portainer_data
docker run -d -p 9000:9000 --name=portainer --restart=always \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v portainer_data:/data \
  portainer/portainer-ce
```

دسترسی: `http://37.152.174.87:9000`

---

## چک‌لیست Deploy

- [ ] اتصال SSH موفق
- [ ] Docker نصب و فعال
- [ ] پروژه از GitHub clone شد
- [ ] فایل .env تنظیم شد
- [ ] Docker containers اجرا شدند
- [ ] Dependencies نصب شد
- [ ] Migrations اجرا شد
- [ ] Seeders اجرا شد
- [ ] سایت در مرورگر باز میشه
- [ ] لاگین موفق
- [ ] API تست شد
- [ ] سهمیه به ادمین داده شد
- [ ] رمز پیش‌فرض تغییر کرد
- [ ] Firewall تنظیم شد
- [ ] بکاپ تنظیم شد

---

## پشتیبانی و تماس

- **GitHub**: https://github.com/sedalcrazy-create/welfare
- **مستندات فاز 1**: PHASE1_README.md
- **مستندات اصلی**: README.md و CLAUDE.md

---

**آخرین به‌روزرسانی:** ۲۰ بهمن ۱۴۰۴
