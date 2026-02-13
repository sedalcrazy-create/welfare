# 🚀 راهنمای Deploy سرور اختصاصی - بات بله

**سرور:** 37.152.174.87
**تاریخ:** 1404/11/25

---

## 📡 اطلاعات سرور

```
IP: 37.152.174.87
OS: Ubuntu 22.04.5 LTS
SSH Port: 22
Web Port: 8083
پروژه: /var/www/welfare
```

---

## 🔐 اتصال به سرور

### روش 1: SSH با کلید (توصیه شده)

```bash
ssh root@37.152.174.87
```

اگر کلید اضافه نشده:
```bash
ssh-copy-id -i ~/.ssh/id_rsa.pub root@37.152.174.87
```

### روش 2: SSH Config (راحت‌تر)

فایل `~/.ssh/config`:
```
Host welfare
    HostName 37.152.174.87
    User root
    Port 22
    IdentityFile ~/.ssh/id_rsa
```

سپس:
```bash
ssh welfare
```

---

## 📦 Deploy بات بله - مرحله به مرحله

### مرحله 1: اتصال به سرور

```bash
ssh root@37.152.174.87
```

### مرحله 2: رفتن به مسیر پروژه

```bash
cd /var/www/welfare
```

### مرحله 3: دریافت آخرین کد

```bash
git fetch origin
git pull origin main
```

**خروجی انتظاری:**
```
Updating fa3bb1c..1f849b9
 BALE_BOT_CONFIG.txt                          |  69 +++++
 BALE_BOT_DEPLOYMENT_TESTING.md               | 701 +++++
 BALE_BOT_QUICK_GUIDE.md                      | 172 ++++
 BALE_BOT_USER_GUIDE.md                       | 643 +++++
 app/Events/PersonnelApproved.php             |  18 +
 app/Events/PersonnelRejected.php             |  20 +
 app/Http/Controllers/Api/BaleWebhookController.php | 140 ++
 app/Listeners/SendBaleApprovalNotification.php | 94 ++
 app/Listeners/SendBaleRejectionNotification.php | 92 ++
 app/Providers/EventServiceProvider.php       |  39 +
 app/Services/BaleBot/BaleCallbackHandler.php | 320 +++
 app/Services/BaleBot/BaleMessageHandler.php  | 380 +++
 app/Services/BaleBot/BaleRegistrationFlow.php | 271 ++
 app/Services/BaleBot/BaleService.php         | 280 ++
 app/Services/BaleBot/BaleSessionManager.php  | 240 ++
 app/Services/BaleBot/MobileNumberNormalizer.php | 65 +
 app/Console/Commands/BaleSetupWebhook.php    | 164 ++
 bootstrap/app.php                            |   7 +
 config/logging.php                           |   8 +
 config/services.php                          |   9 +
 deploy-bale-bot.sh                           | 276 ++
 routes/api.php                               |   1 +
 22 files changed, 4009 insertions(+)
```

### مرحله 4: بررسی و تنظیم .env

```bash
# بررسی فایل .env
cat .env | grep BALE

# اگر تنظیمات بات وجود ندارد، اضافه کنید:
nano .env
```

**تنظیمات بات را اضافه کنید:**
```env
# Bale Bot Configuration
BALE_BOT_TOKEN=1159941038:QJVEuVhVJOZCtQfy4n38uMdTGDMzastM_WE
BALE_BOT_USERNAME=welfarebot
BALE_API_BASE_URL=https://tapi.bale.ai/bot
BALE_WEBHOOK_URL=https://ria.jafamhis.ir/welfare/api/bale/webhook
```

ذخیره: `Ctrl+X`, `Y`, `Enter`

**بررسی مجدد:**
```bash
grep "BALE_" .env
```

### مرحله 5: اجرای اسکریپت Deploy خودکار

```bash
# روش 1: اسکریپت خودکار (توصیه می‌شود)
bash deploy-bale-bot.sh
```

یا:

```bash
# روش 2: دستورات دستی
docker-compose up -d --build
sleep 10
docker-compose exec app composer install --no-dev --optimize-autoloader
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan event:clear
docker-compose restart queue
```

### مرحله 6: Setup Webhook

```bash
docker-compose exec app php artisan bale:setup-webhook
```

**خروجی موفق:**
```
🔧 Setting up Bale Bot webhook...

📍 Webhook URL: https://ria.jafamhis.ir/welfare/api/bale/webhook/1159941038:...

⏳ Sending request to Bale API...

✅ Webhook setup successful!

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📡 Webhook URL: https://ria.jafamhis.ir/welfare/api/bale/webhook/...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

💡 Tip: Use --info option to check webhook status
```

### مرحله 7: بررسی Webhook

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

**⚠️ اگر Pending Update Count > 0 بود:**
- مشکلی در webhook وجود دارد
- URL webhook را بررسی کنید
- لاگ‌ها را چک کنید

### مرحله 8: بررسی وضعیت Services

```bash
docker-compose ps
```

**همه باید Up باشند:**
```
NAME                   STATUS
welfare_app            Up
welfare_nginx          Up
welfare_postgres       Up
welfare_redis          Up
welfare_queue          Up
welfare_scheduler      Up
```

---

## ✅ تست عملکرد

### تست 1: API Health Check

```bash
# از داخل سرور
curl http://localhost:8083/api/status

# از خارج سرور
curl http://37.152.174.87:8083/api/status
```

**خروجی موفق:**
```json
{
  "status": "ok",
  "message": "Welfare API is running",
  "version": "1.0.0",
  "timestamp": "2025-02-14T..."
}
```

### تست 2: Redis

```bash
docker-compose exec app php artisan tinker
```

```php
Redis::ping();
// خروجی: "PONG"

Redis::set('test', 'hello');
Redis::get('test');
// خروجی: "hello"

exit
```

### تست 3: Database

```bash
docker-compose exec app php artisan tinker
```

```php
DB::connection()->getPdo();
// اگر خطا نداد، DB متصل است

\App\Models\Center::count();
// خروجی: 3

exit
```

### تست 4: Queue Worker

```bash
# بررسی queue worker در حال اجرا است
docker-compose ps queue

# لاگ queue
docker-compose logs --tail=20 queue
```

### تست 5: بات در بله

#### 5.1. پیدا کردن بات

1. بله را باز کنید
2. جستجو: `@welfarebot`
3. یا لینک مستقیم: https://ble.ir/welfarebot

#### 5.2. تست /start

1. دکمه **Start** یا **شروع** را بزنید
2. یا `/start` ارسال کنید

**انتظار:**
```
🌟 سلام [نام] عزیز!

به سامانه رزرو مراکز رفاهی بانک ملی خوش آمدید.

از طریق این بات می‌توانید:
✅ درخواست رزرو ثبت کنید
📊 وضعیت درخواست را پیگیری کنید
🏛️ مراکز رفاهی را مشاهده کنید
📄 معرفی‌نامه دریافت کنید
```

با دکمه‌های inline:
- 🎯 ثبت درخواست جدید
- 📊 وضعیت درخواست
- 🏛️ مراکز رفاهی
- ❓ راهنما

#### 5.3. تست ثبت درخواست

1. کلیک: **🎯 ثبت درخواست جدید**
2. کد پرسنلی: `123456`
3. نام: `علی احمدی`
4. کد ملی: `1234567890`
5. موبایل: `09123456789`
6. انتخاب مرکز: **🕌 زائرسرای مشهد**
7. انتخاب دوره
8. تعداد همراهان: **1 نفر**
9. اطلاعات همراه:
   - نام: `فاطمه محمدی`
   - نسبت: **همسر**
   - کد ملی: `0987654321`
   - تاریخ تولد: `1375/05/15`
   - جنسیت: **زن**
10. تأیید نهایی: **✅ تأیید**

**انتظار:**
```
🎉 ثبت‌نام با موفقیت انجام شد!

🆔 کد پیگیری شما:
REQ-0411-XXXX
```

#### 5.4. تست تأیید/رد از پنل ادمین

**ورود به پنل:**
```
URL: http://37.152.174.87:8083/welfare
ایمیل: admin@bankmelli.ir
رمز: password
```

**تأیید درخواست:**
1. منو → Personnel Approvals → Pending Requests
2. پیدا کردن درخواست تست
3. کلیک: **Approve**

**انتظار در بات:**
```
🎉 تبریک! درخواست شما تأیید شد

✅ درخواست رزرو شما با موفقیت تأیید شد.

📋 اطلاعات رزرو:
   کد پیگیری: REQ-0411-XXXX
   ...
```

**رد درخواست (تست دوم):**
1. ثبت درخواست دیگر
2. پنل → Reject
3. دلیل: `تست رد درخواست`

**انتظار در بات:**
```
❌ متأسفانه درخواست شما رد شد

📝 دلیل رد:
تست رد درخواست
```

---

## 🔍 مانیتورینگ و لاگ‌ها

### مشاهده لاگ‌ها

```bash
# لاگ بات بله
docker-compose exec app tail -f storage/logs/bale-bot.log

# لاگ اپلیکیشن
docker-compose exec app tail -f storage/logs/laravel.log

# لاگ queue worker
docker-compose logs -f queue

# لاگ nginx
docker-compose logs -f nginx

# لاگ تمام services
docker-compose logs -f
```

### دستورات مفید

```bash
# تعداد درخواست‌های ثبت شده
docker-compose exec app php artisan tinker
>>> \App\Models\Personnel::count();

# تعداد درخواست‌های از بات
>>> \App\Models\Personnel::where('registration_source', 'bale_bot')->count();

# تعداد pending
>>> \App\Models\Personnel::where('status', 'pending')->count();

# تعداد approved امروز
>>> \App\Models\Personnel::where('status', 'approved')->whereDate('approved_at', today())->count();
```

### بررسی عملکرد

```bash
# استفاده از منابع
docker stats

# فضای دیسک
df -h

# استفاده از RAM
free -h

# بررسی Redis memory
docker-compose exec redis redis-cli INFO memory
```

---

## 🔧 عیب‌یابی

### مشکل: بات پاسخ نمی‌دهد

**بررسی:**
```bash
# 1. وضعیت containers
docker-compose ps

# 2. لاگ app
docker-compose logs --tail=50 app

# 3. لاگ nginx
docker-compose logs --tail=50 nginx

# 4. بررسی webhook
docker-compose exec app php artisan bale:setup-webhook --info
```

**راه‌حل:**
```bash
# ری‌استارت
docker-compose restart app nginx queue

# یا rebuild
docker-compose up -d --build
```

### مشکل: Notification ارسال نمی‌شود

**بررسی:**
```bash
# 1. Queue worker اجراست؟
docker-compose ps queue

# 2. لاگ queue
docker-compose logs --tail=50 queue

# 3. لاگ bale
docker-compose exec app tail -50 storage/logs/bale-bot.log | grep ERROR
```

**راه‌حل:**
```bash
# ری‌استارت queue
docker-compose restart queue

# پاک کردن cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan event:clear
```

### مشکل: Session منقضی می‌شود

**بررسی:**
```bash
# Redis کار می‌کند؟
docker-compose exec app php artisan tinker
>>> Redis::ping();
```

**راه‌حل:**
```bash
docker-compose restart redis app
```

### مشکل: خطای 500

**بررسی:**
```bash
# لاگ Laravel
docker-compose exec app tail -100 storage/logs/laravel.log

# لاگ Nginx
docker-compose logs --tail=100 nginx | grep 500
```

**راه‌حل:**
```bash
# پاک کردن cache
docker-compose exec app php artisan optimize:clear

# rebuild
docker-compose up -d --build
```

---

## 📊 آمار Deploy

**فایل‌های اضافه شده:**
- 18 فایل PHP جدید
- 4 راهنما (Markdown)
- 2 اسکریپت Deploy
- 3,085+ خط کد

**Services جدید:**
- BaleService
- BaleMessageHandler
- BaleCallbackHandler
- BaleRegistrationFlow
- BaleSessionManager
- MobileNumberNormalizer

**Events & Listeners:**
- PersonnelApproved → SendBaleApprovalNotification
- PersonnelRejected → SendBaleRejectionNotification

**Commands:**
- bale:setup-webhook

---

## ✅ چک‌لیست Deploy موفق

قبل از اعلام آماده‌سازی:

- [ ] کد از GitHub pull شده
- [ ] .env تنظیم شده با اطلاعات بات
- [ ] Docker containers rebuild شده‌اند
- [ ] Composer dependencies نصب شدند
- [ ] Cache ها پاک شدند
- [ ] Queue worker ری‌استارت شد
- [ ] Webhook setup شد
- [ ] Webhook info بررسی شد (Pending Count = 0)
- [ ] API health check موفق (200 OK)
- [ ] Redis کار می‌کند (PONG)
- [ ] Database متصل است
- [ ] /start در بات کار می‌کند
- [ ] ثبت درخواست تست موفق
- [ ] تأیید درخواست → notification ارسال شد
- [ ] رد درخواست → notification ارسال شد
- [ ] لاگ‌ها ERROR ندارند

---

## 🎯 مراحل بعدی (پس از Deploy موفق)

### فوری (قبل از Production):

1. **پیاده‌سازی Authorization** (بحرانی ❌)
   - ایجاد Policy ها
   - اضافه کردن authorize() به Controllers

2. **Role-based Access در Routes** (بحرانی ❌)
   - middleware('role:admin')
   - middleware('permission:...')

### بعد از رفع مشکلات بحرانی:

3. رفع N+1 Queries
4. پیاده‌سازی Cache
5. ایجاد Form Requests
6. Refactor Fat Controllers
7. بهبود Validation

---

## 📞 پشتیبانی

**سرور:**
- IP: 37.152.174.87
- Port: 8083
- پروژه: /var/www/welfare

**پنل ادمین:**
- http://37.152.174.87:8083/welfare
- admin@bankmelli.ir / password

**بات بله:**
- @welfarebot
- https://ble.ir/welfarebot

**راهنماها:**
- BALE_BOT_USER_GUIDE.md
- BALE_BOT_QUICK_GUIDE.md
- BALE_BOT_DEPLOYMENT_TESTING.md

---

**موفق باشید! 🚀**

*آخرین بروزرسانی: 1404/11/25*
