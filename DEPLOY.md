# راهنمای Deploy به سرور

## 📡 اطلاعات سرور

| عنوان | مقدار |
|-------|-------|
| **IP** | `37.152.174.87` |
| **Hostname** | `sedal021fsdfs` |
| **OS** | Ubuntu 22.04.5 LTS |
| **CPU** | 8 Core Intel Broadwell |
| **RAM** | 32GB |
| **Disk** | 88GB |

---

## 🚀 مراحل Deploy

### ۱. اتصال به سرور

```bash
# از طریق VPN
ssh root@37.152.174.87
```

> ⚠️ **توجه:** برای اتصال به سرور، VPN نباید فعال باشد. VPN فقط برای Claude Code استفاده می‌شود.

### ۲. کلون پروژه

```bash
cd /var/www
git clone https://github.com/YOUR_USERNAME/welfare-v2.git welfare
cd welfare
```

### ۳. تنظیم Environment

```bash
cp .env.example .env
nano .env
```

تغییرات لازم:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://37.152.174.87:8083

DB_HOST=welfare_postgres
DB_DATABASE=welfare_system
DB_USERNAME=welfare_user
DB_PASSWORD=YOUR_SECURE_PASSWORD

REDIS_HOST=welfare_redis
```

### ۴. اجرای Docker Compose

```bash
# ساخت و اجرا
docker-compose -f docker-compose.production.yml up -d --build

# بررسی وضعیت
docker-compose -f docker-compose.production.yml ps
```

### ۵. نصب وابستگی‌ها و Migration

```bash
# ورود به کانتینر
docker-compose -f docker-compose.production.yml exec app sh

# نصب composer
composer install --no-dev --optimize-autoloader

# کلید برنامه
php artisan key:generate

# Migration و Seed
php artisan migrate --seed

# کش
php artisan config:cache
php artisan route:cache
php artisan view:cache

# دسترسی‌ها
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# خروج
exit
```

### ۶. بررسی سرویس‌ها

```bash
# لاگ‌ها
docker-compose -f docker-compose.production.yml logs -f

# تست اتصال
curl http://localhost:8083
```

---

## 📊 پورت‌های استفاده شده

| سرویس | پورت داخلی | پورت خارجی |
|-------|------------|------------|
| Nginx | 80 | **8083** |
| PostgreSQL | 5432 | 5434 |
| Redis | 6379 | 6381 |
| PHP-FPM | 9000 | - |

---

## 🔄 به‌روزرسانی

```bash
cd /var/www/welfare

# دریافت تغییرات
git pull origin main

# ورود به کانتینر
docker-compose -f docker-compose.production.yml exec app sh

# به‌روزرسانی
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exit

# ریستارت
docker-compose -f docker-compose.production.yml restart app queue
```

---

## 💾 بکاپ

### بکاپ دستی

```bash
# بکاپ دیتابیس
docker-compose -f docker-compose.production.yml exec postgres \
    pg_dump -U welfare_user welfare_system > backup_$(date +%Y%m%d).sql

# بکاپ فایل‌ها
tar -czf welfare_files_$(date +%Y%m%d).tar.gz storage/
```

### بکاپ خودکار (Crontab)

```bash
crontab -e
```

اضافه کنید:
```cron
# بکاپ روزانه ساعت ۳ صبح
0 3 * * * /var/www/welfare/scripts/backup.sh
```

---

## 🔍 عیب‌یابی

### مشاهده لاگ‌ها

```bash
# همه لاگ‌ها
docker-compose -f docker-compose.production.yml logs -f

# فقط app
docker-compose -f docker-compose.production.yml logs -f app

# لاگ Laravel
docker-compose -f docker-compose.production.yml exec app tail -f storage/logs/laravel.log
```

### ریستارت سرویس‌ها

```bash
# همه
docker-compose -f docker-compose.production.yml restart

# فقط app
docker-compose -f docker-compose.production.yml restart app
```

### پاک کردن کش

```bash
docker-compose -f docker-compose.production.yml exec app php artisan cache:clear
docker-compose -f docker-compose.production.yml exec app php artisan config:clear
docker-compose -f docker-compose.production.yml exec app php artisan view:clear
```

---

## 📋 چک‌لیست Deploy

- [ ] VPN غیرفعال برای اتصال به سرور
- [ ] SSH به سرور
- [ ] کلون پروژه
- [ ] تنظیم .env
- [ ] Docker Compose up
- [ ] Composer install
- [ ] php artisan key:generate
- [ ] php artisan migrate --seed
- [ ] تست http://37.152.174.87:8083
- [ ] تنظیم Cron بکاپ
- [ ] تنظیم دامنه (اختیاری)

---

## 🌐 تنظیم دامنه (اختیاری)

### Nginx Proxy

در `/etc/nginx/sites-available/welfare`:

```nginx
server {
    listen 80;
    server_name welfare.darmanjoo.ir;

    location / {
        proxy_pass http://127.0.0.1:8083;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
ln -s /etc/nginx/sites-available/welfare /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### SSL با Certbot

```bash
certbot --nginx -d welfare.darmanjoo.ir
```

---

## 📞 پشتیبانی

در صورت بروز مشکل:
1. لاگ‌ها را بررسی کنید
2. با دستور `docker-compose ps` وضعیت سرویس‌ها را ببینید
3. در صورت نیاز ریستارت کنید
