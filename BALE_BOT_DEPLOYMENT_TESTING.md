# 🚀 راهنمای Deploy و تست بات بله

**تاریخ:** 1404/11/25
**نسخه:** 1.0

---

## 📋 فهرست

1. [پیش‌نیازها](#پیشنیازها)
2. [تنظیمات اولیه](#تنظیمات-اولیه)
3. [Deploy به سرور](#deploy-به-سرور)
4. [Setup Webhook](#setup-webhook)
5. [تست عملکرد](#تست-عملکرد)
6. [مانیتورینگ](#مانیتورینگ)
7. [عیب‌یابی](#عیبیابی)

---

## 🔑 پیش‌نیازها

### 1. دریافت Bot Token از بله

قبل از شروع، باید یک بات در بله ایجاد کنید:

1. در بله، به بات `@BotFather` (یا معادل آن در بله) پیام دهید
2. دستور `/newbot` را ارسال کنید
3. نام بات را وارد کنید (مثال: `Welfare Bot`)
4. Username بات را وارد کنید (مثال: `BankMelliWelfareBot`)
   - باید با `bot` تمام شود
   - فقط حروف انگلیسی، اعداد و underscore
5. Token دریافت کنید (مثال: `1234567890:ABCdefGHIjklMNOpqrsTUVwxyz`)

**نکته:** این Token را در جای امنی ذخیره کنید!

### 2. بررسی سرور

سرور باید این موارد را داشته باشد:
- ✅ Docker و Docker Compose نصب شده
- ✅ Git نصب شده
- ✅ دسترسی به اینترنت (برای دریافت API بله)
- ✅ Domain یا IP عمومی با HTTPS (برای webhook)
- ✅ Port 80/443 باز (برای webhook)

---

## ⚙️ تنظیمات اولیه

### 1. تنظیم فایل `.env`

روی سرور، فایل `.env` را ویرایش کنید:

```bash
cd /var/www/welfare
nano .env
```

این متغیرها را اضافه/ویرایش کنید:

```env
# Bale Bot Configuration
BALE_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
BALE_BOT_USERNAME=BankMelliWelfareBot
BALE_API_BASE_URL=https://tapi.bale.ai/bot
BALE_WEBHOOK_URL=https://ria.jafamhis.ir/welfare/api/bale/webhook

# Redis (برای Session Management)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue (برای Events)
QUEUE_CONNECTION=redis
```

**نکات مهم:**
- `BALE_BOT_TOKEN`: Token دریافتی از BotFather بله
- `BALE_BOT_USERNAME`: Username بات (بدون @)
- `BALE_WEBHOOK_URL`: URL عمومی سرور + مسیر webhook
  - باید با HTTPS باشد
  - فرمت: `https://DOMAIN/welfare/api/bale/webhook`

### 2. بررسی URL Webhook

URL کامل webhook به این صورت خواهد بود:
```
https://ria.jafamhis.ir/welfare/api/bale/webhook/[TOKEN]
```

مثال:
```
https://ria.jafamhis.ir/welfare/api/bale/webhook/1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
```

**امنیت:** Token در URL قرار می‌گیرد، پس webhook فقط با Token صحیح قابل دسترسی است.

---

## 🚀 Deploy به سرور

### گزینه 1: استفاده از اسکریپت Deploy خودکار

```bash
# با دسترسی root
sudo bash /var/www/welfare/deploy.sh
```

این اسکریپت خودکار:
- ✅ آخرین کد را از GitHub می‌گیرد
- ✅ Docker containers را build می‌کند
- ✅ Dependencies نصب می‌کنند
- ✅ Migrations اجرا می‌شوند
- ✅ Cache پاک می‌شود
- ✅ Services ری‌استارت می‌شوند

### گزینه 2: Deploy دستی

```bash
# 1. رفتن به مسیر پروژه
cd /var/www/welfare

# 2. دریافت آخرین تغییرات
git fetch origin
git pull origin main

# 3. بررسی فایل .env
ls -la .env

# 4. Build و اجرای containers
docker-compose up -d --build

# 5. صبر برای آماده شدن
sleep 10

# 6. نصب dependencies
docker-compose exec app composer install --no-dev --optimize-autoloader

# 7. اجرای migrations (اگر نیاز باشد)
docker-compose exec app php artisan migrate --force

# 8. پاک‌سازی cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan event:clear

# 9. ری‌استارت queue worker (مهم!)
docker-compose restart queue

# 10. بررسی وضعیت
docker-compose ps
```

### بررسی لاگ‌ها

```bash
# لاگ اپلیکیشن
docker-compose logs -f app

# لاگ queue worker
docker-compose logs -f queue

# لاگ nginx
docker-compose logs -f nginx
```

---

## 🔗 Setup Webhook

بعد از deploy موفق، باید webhook را به بله معرفی کنید.

### روش 1: استفاده از Artisan Command (توصیه می‌شود)

```bash
# Setup webhook
docker-compose exec app php artisan bale:setup-webhook

# بررسی اطلاعات webhook
docker-compose exec app php artisan bale:setup-webhook --info

# حذف webhook (اگر نیاز باشد)
docker-compose exec app php artisan bale:setup-webhook --delete
```

**خروجی موفق:**
```
🔧 Setting up Bale Bot webhook...

📍 Webhook URL: https://ria.jafamhis.ir/welfare/api/bale/webhook/1234567890:ABC...

⏳ Sending request to Bale API...

✅ Webhook setup successful!

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📡 Webhook URL: https://ria.jafamhis.ir/welfare/api/bale/webhook/1234567890:ABC...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

💡 Tip: Use --info option to check webhook status
```

### روش 2: Setup دستی با cURL

```bash
# متغیرها
TOKEN="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz"
WEBHOOK_URL="https://ria.jafamhis.ir/welfare/api/bale/webhook/${TOKEN}"

# ارسال درخواست setWebhook
curl -X POST "https://tapi.bale.ai/bot${TOKEN}/setWebhook" \
  -H "Content-Type: application/json" \
  -d "{\"url\":\"${WEBHOOK_URL}\"}"

# بررسی وضعیت webhook
curl -X POST "https://tapi.bale.ai/bot${TOKEN}/getWebhookInfo"
```

### بررسی webhook

```bash
docker-compose exec app php artisan bale:setup-webhook --info
```

**خروجی موفق:**
```
📊 Fetching webhook information...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📡 Webhook Information
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

URL: https://ria.jafamhis.ir/welfare/api/bale/webhook/...
Has Custom Certificate: No
Pending Update Count: 0

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**نکته:** اگر `Pending Update Count` بالا باشد، یعنی مشکلی در webhook وجود دارد.

---

## ✅ تست عملکرد

### مرحله 1: تست API Health

```bash
# تست از داخل سرور
curl http://localhost:8083/api/status

# تست از خارج سرور
curl https://ria.jafamhis.ir/welfare/api/status
```

**خروجی موفق:**
```json
{
  "status": "ok",
  "message": "Welfare API is running",
  "version": "1.0.0",
  "timestamp": "2025-02-14T10:30:00Z"
}
```

### مرحله 2: تست Redis

```bash
docker-compose exec app php artisan tinker
```

در Tinker:
```php
// تست Redis
Redis::ping();
// خروجی: "PONG"

// تست set/get
Redis::set('test', 'hello');
Redis::get('test');
// خروجی: "hello"

// خروج
exit
```

### مرحله 3: تست بات در بله

#### 3.1. پیدا کردن بات
1. در بله، جستجو کنید: `@BankMelliWelfareBot` (یا username بات شما)
2. روی نام بات کلیک کنید
3. دکمه **Start** یا **شروع** را بزنید

#### 3.2. تست دستور `/start`

**انتظار:**
- بات باید پیام خوش‌آمدگویی ارسال کند
- منوی اصلی با دکمه‌های inline نمایش داده شود

**مثال پیام:**
```
🌟 سلام علی عزیز!

به سامانه رزرو مراکز رفاهی بانک ملی خوش آمدید.

از طریق این بات می‌توانید:
✅ درخواست رزرو ثبت کنید
📊 وضعیت درخواست را پیگیری کنید
🏛️ مراکز رفاهی را مشاهده کنید
📄 معرفی‌نامه دریافت کنید

برای شروع، یکی از دکمه‌های زیر را انتخاب کنید:
```

با دکمه‌ها:
- 🎯 ثبت درخواست جدید
- 📊 وضعیت درخواست
- 🏛️ مراکز رفاهی
- ❓ راهنما

#### 3.3. تست ثبت درخواست کامل

1. **شروع ثبت:** کلیک روی "🎯 ثبت درخواست جدید"
2. **کد پرسنلی:** وارد کنید `123456`
3. **نام کامل:** وارد کنید `علی احمدی`
4. **کد ملی:** وارد کنید `1234567890`
5. **موبایل:** وارد کنید `09123456789`
6. **انتخاب مرکز:** کلیک روی یکی از مراکز
7. **انتخاب دوره:** کلیک روی یکی از دوره‌ها
8. **تعداد همراهان:** مثلاً "1 نفر"
9. **اطلاعات همراه:**
   - نام: `فاطمه محمدی`
   - نسبت: کلیک روی "همسر"
   - کد ملی: `0987654321`
   - تاریخ تولد: `1375/05/15`
   - جنسیت: کلیک روی "زن"
10. **تأیید نهایی:** بررسی خلاصه + کلیک روی "✅ تأیید و ثبت نهایی"

**انتظار:**
- پیام موفقیت ارسال شود
- کد پیگیری نمایش داده شود (مثال: `REQ-0411-1234`)

#### 3.4. تست پیگیری وضعیت

1. دستور `/status` ارسال کنید
2. یا کد پیگیری را ارسال کنید: `REQ-0411-1234`

**انتظار:**
- وضعیت درخواست نمایش داده شود
- اطلاعات کامل درخواست موجود باشد

#### 3.5. تست مراکز رفاهی

1. دستور `/centers` ارسال کنید
2. یا کلیک روی "🏛️ مراکز رفاهی"

**انتظار:**
- لیست 3 مرکز نمایش داده شود
- اطلاعات هر مرکز کامل باشد

#### 3.6. تست راهنما

1. دستور `/help` ارسال کنید
2. یا کلیک روی "❓ راهنما"

**انتظار:**
- راهنمای کامل نمایش داده شود
- لیست دستورات موجود باشد

### مرحله 4: تست تأیید/رد از پنل ادمین

#### 4.1. ورود به پنل ادمین

```
URL: https://ria.jafamhis.ir/welfare
ایمیل: admin@bankmelli.ir
رمز: password
```

#### 4.2. تأیید درخواست

1. از منو: **Personnel Approvals** → **Pending Requests**
2. درخواست تست را پیدا کنید (کد پیگیری: `REQ-0411-1234`)
3. کلیک روی **View Details**
4. کلیک روی **Approve**

**انتظار:**
- در بات، پیام تأیید ارسال شود:
  ```
  🎉 تبریک! درخواست شما تأیید شد

  ✅ درخواست رزرو شما با موفقیت تأیید شد.
  ...
  ```

#### 4.3. رد درخواست

1. یک درخواست دیگر ثبت کنید
2. از پنل ادمین، کلیک روی **Reject**
3. دلیل رد وارد کنید: `تست رد درخواست`
4. Submit کنید

**انتظار:**
- در بات، پیام رد ارسال شود:
  ```
  ❌ متأسفانه درخواست شما رد شد

  📝 دلیل رد:
  تست رد درخواست
  ...
  ```

### مرحله 5: بررسی لاگ‌ها

```bash
# لاگ بات بله
docker-compose exec app tail -f storage/logs/bale-bot.log

# لاگ Laravel
docker-compose exec app tail -f storage/logs/laravel.log

# لاگ Nginx
docker-compose logs -f nginx
```

**چه چیزهایی باید در لاگ باشد:**
- ✅ Webhook requests دریافت شده
- ✅ پردازش پیام‌های کاربران
- ✅ ثبت درخواست‌ها
- ✅ ارسال notifications
- ❌ خطاها (باید صفر باشد!)

---

## 📊 مانیتورینگ

### چک‌لیست مانیتورینگ روزانه

```bash
# 1. بررسی وضعیت containers
docker-compose ps

# 2. بررسی webhook
docker-compose exec app php artisan bale:setup-webhook --info

# 3. بررسی pending updates
# اگر Pending Update Count > 10 بود، مشکل وجود دارد

# 4. بررسی لاگ خطاها
docker-compose exec app tail -100 storage/logs/bale-bot.log | grep ERROR

# 5. بررسی Redis
docker-compose exec app php artisan tinker
>>> Redis::ping();

# 6. بررسی Queue
docker-compose exec app php artisan queue:work --once

# 7. بررسی Database
docker-compose exec app php artisan tinker
>>> \App\Models\Personnel::count();
>>> \App\Models\Personnel::pending()->count();
```

### نمایش آمار

```bash
# تعداد کل درخواست‌ها
docker-compose exec app php artisan tinker
>>> \App\Models\Personnel::count();

# تعداد درخواست‌های از بله
>>> \App\Models\Personnel::where('registration_source', 'bale_bot')->count();

# تعداد pending
>>> \App\Models\Personnel::where('status', 'pending')->count();

# تعداد approved امروز
>>> \App\Models\Personnel::where('status', 'approved')->whereDate('approved_at', today())->count();
```

---

## 🔧 عیب‌یابی

### مشکل 1: بات پاسخ نمی‌دهد

**علائم:**
- کاربر پیام می‌فرستد، بات پاسخ نمی‌دهد
- دستور `/start` کار نمی‌کند

**بررسی:**
```bash
# 1. وضعیت containers
docker-compose ps
# همه باید "Up" باشند

# 2. لاگ app
docker-compose logs --tail=50 app

# 3. لاگ nginx
docker-compose logs --tail=50 nginx

# 4. تست webhook
curl -X POST "https://ria.jafamhis.ir/welfare/api/bale/webhook/[TOKEN]" \
  -H "Content-Type: application/json" \
  -d '{"message":{"text":"test"}}'
```

**راه‌حل‌ها:**
1. ری‌استارت containers:
   ```bash
   docker-compose restart app nginx
   ```

2. بررسی webhook:
   ```bash
   docker-compose exec app php artisan bale:setup-webhook --info
   ```

3. Setup دوباره webhook:
   ```bash
   docker-compose exec app php artisan bale:setup-webhook
   ```

### مشکل 2: Notifications ارسال نمی‌شوند

**علائم:**
- ادمین درخواست را تأیید/رد می‌کند
- بات به کاربر پیام نمی‌فرستد

**بررسی:**
```bash
# 1. Queue worker در حال اجرا است؟
docker-compose ps queue

# 2. لاگ queue
docker-compose logs --tail=50 queue

# 3. تست Event
docker-compose exec app php artisan tinker
>>> $p = \App\Models\Personnel::first();
>>> event(new \App\Events\PersonnelApproved($p));

# 4. بررسی لاگ bale
docker-compose exec app tail -f storage/logs/bale-bot.log
```

**راه‌حل‌ها:**
1. ری‌استارت queue worker:
   ```bash
   docker-compose restart queue
   ```

2. پاک کردن cache:
   ```bash
   docker-compose exec app php artisan cache:clear
   docker-compose exec app php artisan config:clear
   docker-compose exec app php artisan event:clear
   ```

3. اجرای queue به صورت دستی برای تست:
   ```bash
   docker-compose exec app php artisan queue:work --once
   ```

### مشکل 3: Session منقضی می‌شود خیلی زود

**علائم:**
- کاربر در حین ثبت‌نام session از دست می‌دهد
- پیام "Session منقضی شده" زود نمایش داده می‌شود

**بررسی:**
```bash
# 1. Redis کار می‌کند؟
docker-compose exec app php artisan tinker
>>> Redis::ping();

# 2. TTL Session چقدر است؟
>>> Redis::ttl('bale_session:123456');
# باید 1800 (30 دقیقه) باشد
```

**راه‌حل:**
1. بررسی Redis:
   ```bash
   docker-compose ps redis
   docker-compose logs redis
   ```

2. ری‌استارت Redis:
   ```bash
   docker-compose restart redis app
   ```

### مشکل 4: خطای 500 در webhook

**علائم:**
- در لاگ بله: HTTP 500 error
- بات گاهی پاسخ می‌دهد، گاهی نه

**بررسی:**
```bash
# لاگ Laravel
docker-compose exec app tail -f storage/logs/laravel.log

# لاگ Nginx
docker-compose logs --tail=100 nginx | grep "500"
```

**راه‌حل:**
1. افزایش memory limit PHP:
   ```bash
   # ویرایش docker-compose.yml یا php.ini
   ```

2. بررسی Database connection:
   ```bash
   docker-compose exec app php artisan tinker
   >>> DB::connection()->getPdo();
   ```

3. Clear cache و optimize:
   ```bash
   docker-compose exec app php artisan optimize:clear
   docker-compose exec app php artisan config:cache
   ```

### مشکل 5: Inline Keyboards کار نمی‌کنند

**علائم:**
- دکمه‌ها نمایش داده می‌شوند
- کلیک روی دکمه اثری ندارد

**بررسی:**
```bash
# لاگ callback handler
docker-compose exec app tail -f storage/logs/bale-bot.log | grep "callback"
```

**راه‌حل:**
1. بررسی callback_data format
2. تست callback handler مستقیم
3. بررسی لاگ‌ها برای exception

---

## 📈 Metrics و Performance

### بررسی عملکرد

```bash
# تعداد requests امروز
docker-compose exec app php artisan tinker
>>> \App\Models\Personnel::whereDate('created_at', today())->count();

# میانگین زمان پاسخ
docker-compose logs nginx | grep "POST /welfare/api/bale/webhook" | tail -20

# استفاده از RAM
docker stats welfare_app

# استفاده از Redis
docker-compose exec redis redis-cli INFO memory
```

---

## ✅ چک‌لیست Deploy موفق

قبل از اعلام آماده‌سازی:

- [ ] `.env` تنظیم شده
- [ ] Docker containers اجرا هستند
- [ ] Migrations اجرا شده‌اند
- [ ] Redis کار می‌کند
- [ ] Queue worker اجرا است
- [ ] Webhook setup شده
- [ ] `/start` در بات کار می‌کند
- [ ] ثبت درخواست تست موفق
- [ ] پیگیری وضعیت کار می‌کند
- [ ] Notification تأیید ارسال می‌شود
- [ ] Notification رد ارسال می‌شود
- [ ] لاگ‌ها خطا ندارند
- [ ] راهنمای کاربر در دسترس است

---

## 📞 پشتیبانی

اگر مشکلی بود:

1. لاگ‌ها را بررسی کنید
2. عیب‌یابی را دنبال کنید
3. GitHub Issues ایجاد کنید
4. با تیم توسعه تماس بگیرید

---

**موفق باشید! 🚀**

*نسخه 1.0 - 1404/11/25*
