# 🚀 راهنمای Deploy روی سرور

## 🎉 وضعیت نهایی: همه چیز آماده است!

**✅ Deploy با موفقیت انجام شد - تاریخ: 1405/11/26 (2026-02-14)**

---

## ✅ خلاصه: تمام مراحل انجام شده

### 1️⃣ Git & Code
- ✅ تغییرات از GitHub دریافت شد (17 فایل، 1,917 خط)
- ✅ سیستم مهمانان کامل شد (migrations, models, controllers, views)

### 2️⃣ Docker
- ✅ Redis Extension نصب شد (از GitHub)
- ✅ همه 6 کانتینر بالا و سالم هستند
- ✅ Port conflicts حل شد (8083, 5434, 6381)

### 3️⃣ Database & Cache
- ✅ PostgreSQL متصل (16.11, 25 جدول، 1.34 MB)
- ✅ Redis متصل و سالم
- ✅ Migrations اجرا شد (2 migration جدید)

### 4️⃣ Permissions & Config
- ✅ Storage permissions تنظیم شد (www-data:www-data)
- ✅ Config cache پاک شد
- ✅ .env تنظیم شد (DB_HOST=postgres, REDIS_HOST=redis)

---

## 🌐 دسترسی به سامانه

### URL‌های عمومی:
- **HTTPS:** https://ria.jafamhis.ir/welfare/login
- **HTTP:** http://37.152.174.87:8083/welfare/login

### اطلاعات ورود پیش‌فرض:
```
ایمیل: admin@bankmelli.ir
رمز: password
```

### نحوه تست سیستم مهمانان:
1. وارد سیستم شوید
2. به بخش **پرسنل** بروید
3. یک پرسنل را انتخاب کنید
4. روی تب **"مهمانان"** کلیک کنید
5. مهمان جدید اضافه کنید

---

## 📊 وضعیت فعلی سرور

### کانتینرهای فعال (6 عدد):
```
✅ welfare_app         - PHP-FPM
✅ welfare_nginx       - Web Server (port 8083)
✅ welfare_postgres    - Database (port 5434)
✅ welfare_redis       - Cache/Queue (port 6381)
✅ welfare_queue       - Queue Worker
✅ welfare_scheduler   - Task Scheduler
```

### پورت‌های اختصاصی:
- **Web:** 8083 → 80 (nginx)
- **PostgreSQL:** 5434 → 5432
- **Redis:** 6381 → 6379

---

## 🔧 مشکلات حل شده

### خطا 1: Redis PECL نصب نشد
**راه حل:** دانلود و compile از GitHub
```dockerfile
RUN cd /tmp \
    && curl -L https://github.com/phpredis/phpredis/archive/6.0.2.tar.gz | tar xz \
    && cd phpredis-6.0.2 \
    && phpize && ./configure && make && make install \
    && docker-php-ext-enable redis
```

### خطا 2: Port Conflicts
**راه حل:** تغییر پورت‌ها بدون تأثیر روی پروژه‌های دیگر
- PostgreSQL: 5433 → 5434
- Nginx: 8080 → 8083

### خطا 3: Parse Error در bootstrap/app.php
**راه حل:** حذف بخش withEvents (Laravel 11 نیازی ندارد)

### خطا 4: Database/Redis Connection
**راه حل:** تنظیم .env با نام‌های Docker service
- DB_HOST=postgres (نه IP)
- REDIS_HOST=redis (نه IP)

### خطا 5: Permission Denied (500 Error)
**راه حل:** تنظیم مالکیت storage
```bash
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

---

## 🛠️ دستورات مفید

### بررسی وضعیت:
```bash
ssh root@37.152.174.87
cd /var/www/welfare

# وضعیت کانتینرها
docker compose ps

# لاگ‌ها
docker logs welfare_app
docker logs welfare_nginx

# تست اتصالات
docker exec welfare_app php artisan db:show
docker exec welfare_app php artisan tinker --execute="use Illuminate\Support\Facades\Redis; echo Redis::connection()->ping();"
```

### پاک کردن cache:
```bash
docker exec welfare_app php artisan config:clear
docker exec welfare_app php artisan cache:clear
docker exec welfare_app php artisan view:clear
docker exec welfare_app php artisan route:clear
```

### Migrations:
```bash
# اجرای migrations
docker exec welfare_app php artisan migrate --force

# وضعیت migrations
docker exec welfare_app php artisan migrate:status
```

### Restart:
```bash
# Restart تک‌تک containers
docker compose restart app nginx

# Restart همه
docker compose restart

# Rebuild (اگر Dockerfile تغییر کرد)
docker compose down
docker compose up -d --build
```

---

## 📝 یادداشت‌های مهم

1. **بقیه پروژه‌ها:** تمام 25 کانتینر پروژه‌های دیگر سالم و بدون تغییر هستند
2. **Nginx اصلی:** سرور nginx اصلی (/etc/nginx) بدون تغییر است
3. **Reverse Proxy:** تنظیمات `/etc/nginx/sites-enabled/ria.jafamhis.ir` بررسی و تأیید شد
4. **Production Mode:** APP_DEBUG=false برای امنیت

---

**✅ وضعیت: آماده برای استفاده در Production!**

💡 برای گزارش مشکل یا سؤال: بررسی لاگ‌ها در `storage/logs/laravel.log`
