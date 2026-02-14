# 🚀 دستورالعمل راه‌اندازی سیستم مهمانان

## گام 1: اطمینان از اجرای Docker Desktop

1. Docker Desktop را اجرا کنید
2. منتظر بمانید تا Docker کاملاً آماده شود (آیکن سبز شود)

---

## گام 2: اجرای اسکریپت نصب

### در Windows:
دابل‌کلیک روی فایل:
```
setup-guest-system.bat
```

یا در PowerShell/CMD:
```bash
.\setup-guest-system.bat
```

### در Linux/Mac:
```bash
chmod +x setup-guest-system.sh
./setup-guest-system.sh
```

---

## گام 3: دستی (اگر اسکریپت کار نکرد)

```bash
# 1. راه‌اندازی Docker
docker-compose up -d

# 2. صبر 5 ثانیه تا دیتابیس آماده شود
# (منتظر بمانید...)

# 3. اجرای migrations
docker-compose exec app php artisan migrate

# 4. بررسی migrations
docker-compose exec app php artisan migrate:status
```

---

## گام 4: تست سیستم

1. مرورگر را باز کنید: http://localhost:8080
2. لاگین کنید:
   - **ایمیل:** admin@bankmelli.ir
   - **رمز عبور:** password

3. به بخش "پرسنل" بروید
4. یک پرسنل را انتخاب کنید (یا جدید بسازید)
5. در صفحه نمایش پرسنل، بخش **"مهمانان"** را خواهید دید
6. روی **"افزودن مهمان"** کلیک کنید
7. مهمان جدید اضافه کنید و تست کنید

---

## مشکلات متداول

### ❌ "Docker در حال اجرا نیست"
**راه حل:** Docker Desktop را اجرا کنید و منتظر بمانید تا آماده شود.

### ❌ "Error: could not find driver"
**راه حل:**
```bash
docker-compose down
docker-compose up -d --build
```

### ❌ صفحه 500 Error
**راه حل:**
```bash
# بررسی لاگ‌ها
docker-compose logs app

# پاک کردن cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

### ❌ "SQLSTATE[42S02]: Base table or view not found"
**راه حل:** migrations اجرا نشده. دوباره تلاش کنید:
```bash
docker-compose exec app php artisan migrate --force
```

---

## بررسی موفقیت‌آمیز بودن نصب

```bash
# چک کردن جداول جدید
docker-compose exec app php artisan tinker

>>> \Schema::hasTable('guests')
=> true

>>> \Schema::hasTable('personnel_guests')
=> true

>>> \Schema::hasColumn('lottery_entries', 'selected_guest_ids')
=> true

>>> exit
```

---

## مستندات کامل

برای اطلاعات بیشتر:
- **راهنمای کامل:** `GUEST_SYSTEM_GUIDE.md`
- **مشخصات سیستم:** `PERSONNEL_GUESTS_SPEC.md`

---

## تست سریع در Tinker

```bash
docker-compose exec app php artisan tinker
```

```php
# ساخت یک مهمان
$guest = \App\Models\Guest::create([
    'national_code' => '1234567890',
    'full_name' => 'تست مهمان',
    'relation' => 'همسر',
    'gender' => 'male',
]);

# یافتن یک پرسنل
$personnel = \App\Models\Personnel::first();

# اتصال مهمان به پرسنل
$personnel->guests()->attach($guest->id);

# لیست مهمانان
$personnel->guests;

# بررسی نوع مهمان
$guest->isBankAffiliated();  // true (همسر بانکی است)

# حذف
$personnel->guests()->detach($guest->id);
```

---

✅ **پس از تکمیل این مراحل، سیستم مدیریت مهمانان آماده استفاده است!**
