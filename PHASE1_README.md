# فاز 1: سیستم ساده صدور معرفی‌نامه

## تاریخ: ۲۰ بهمن ۱۴۰۴

---

## تغییرات اصلی

### 🎯 هدف فاز 1
ساده‌سازی سیستم برای صدور معرفی‌نامه **بدون قرعه‌کشی** با تأیید دستی توسط مدیر/کارفرما.

---

## ویژگی‌های پیاده‌سازی شده

### ✅ 1. ثبت درخواست (2 روش)

#### الف) از طریق بات بله:
```http
POST /api/bale/register
{
    "full_name": "علی احمدی",
    "national_code": "1234567890",
    "phone": "09123456789",
    "family_count": 4,
    "preferred_center_id": 1,
    "bale_user_id": "123456"
}
```

#### ب) ثبت دستی در پنل:
- مسیر: `/personnel-requests/create`
- فرم ثبت با تمام اطلاعات

### ✅ 2. مدیریت درخواست‌ها
- لیست درخواست‌ها با فیلتر (وضعیت، مرکز، منبع)
- مشاهده جزئیات درخواست
- تأیید/رد درخواست توسط مدیر
- بررسی دستی اطلاعات

### ✅ 3. صدور معرفی‌نامه
- صدور معرفی‌نامه برای درخواست‌های تأیید شده
- تولید کد یونیک (مثال: MHD-0411-0001)
- کاهش خودکار سهمیه مدیر
- چاپ/دانلود PDF

### ✅ 4. سیستم سهمیه‌بندی
- هر مدیر سهمیه محدود دارد
- کاهش خودکار با صدور معرفی‌نامه
- بازگشت سهمیه در صورت لغو
- جلوگیری از صدور بیش از سهمیه

### ✅ 5. API برای بات بله
```http
# دریافت لیست مراکز
GET /api/bale/centers

# ثبت درخواست
POST /api/bale/register

# چک وضعیت
POST /api/bale/check-status
{
    "identifier": "1234567890",
    "identifier_type": "national_code"
}

# دریافت معرفی‌نامه‌ها
POST /api/bale/letters
{
    "national_code": "1234567890"
}
```

---

## تغییرات Database

### 🗄️ Migrations جدید

#### 1. `simplify_personnel_for_phase1`
```sql
-- اضافه شده:
- status (pending, approved, rejected)
- registration_source (manual, bale_bot, web)
- preferred_center_id
- notes
- tracking_code (کد پیگیری)

-- تغییر یافته:
- province_id → nullable
- employee_code → nullable
```

#### 2. `create_introduction_letters_table`
```sql
CREATE TABLE introduction_letters (
    id bigint,
    letter_code varchar(30) UNIQUE,  -- MHD-0411-0001
    personnel_id bigint,
    center_id bigint,
    issued_by_user_id bigint,
    family_count integer,
    notes text,
    valid_from varchar(10),
    valid_until varchar(10),
    issued_at timestamp,
    used_at timestamp,
    status enum('active', 'used', 'cancelled', 'expired'),
    cancellation_reason text,
    cancelled_by_user_id bigint,
    cancelled_at timestamp
);
```

#### 3. `add_quota_to_users_table`
```sql
ALTER TABLE users ADD COLUMNS (
    quota_total integer DEFAULT 0,
    quota_used integer DEFAULT 0,
    quota_remaining integer GENERATED (quota_total - quota_used),
    province_id bigint
);
```

---

## Models جدید

### 📦 IntroductionLetter
```php
// Relations
- personnel()
- center()
- issuedBy()
- cancelledBy()

// Methods
- markAsUsed()
- cancel($reason, $userId)
- isActive()
- generateLetterCode($center)  // Static
```

### 📝 Personnel (به‌روزرسانی شده)
```php
// Constants
STATUS_PENDING = 'pending'
STATUS_APPROVED = 'approved'
STATUS_REJECTED = 'rejected'

SOURCE_MANUAL = 'manual'
SOURCE_BALE_BOT = 'bale_bot'
SOURCE_WEB = 'web'

// New Relations
- introductionLetters()
- preferredCenter()

// New Scopes
- scopePending()
- scopeApproved()
- scopeRejected()
- scopeFromBaleBot()

// New Methods
- generateTrackingCode()  // Static
```

### 👤 User (به‌روزرسانی شده)
```php
// New Fields
- quota_total
- quota_used
- quota_remaining (virtual)

// New Methods
- getQuotaRemaining()
- hasQuotaAvailable($count)
- incrementQuotaUsed($count)
- decrementQuotaUsed($count)
- introductionLetters()
```

---

## Controllers جدید

### 1. PersonnelRequestController (Web Panel)
```php
- index()       // لیست درخواست‌ها
- create()      // فرم ثبت دستی
- store()       // ذخیره درخواست
- show()        // جزئیات
- edit()        // ویرایش
- update()      // به‌روزرسانی
- approve()     // تأیید
- reject()      // رد
- destroy()     // حذف
```

### 2. IntroductionLetterController (Web Panel)
```php
- index()       // لیست معرفی‌نامه‌ها
- create()      // فرم صدور
- store()       // صدور معرفی‌نامه
- show()        // جزئیات
- cancel()      // لغو
- markAsUsed()  // علامت‌گذاری به عنوان استفاده شده
- print()       // چاپ PDF
```

### 3. PersonnelRequestController (API)
```php
- register()      // ثبت از بات بله
- checkStatus()   // چک وضعیت
- getLetters()    // دریافت معرفی‌نامه‌ها
- getCenters()    // لیست مراکز
```

---

## مسیرها (Routes)

### Web Panel:
```php
/personnel-requests             // لیست درخواست‌ها
/personnel-requests/create      // ثبت دستی
/personnel-requests/{id}        // جزئیات
/personnel-requests/{id}/edit   // ویرایش
/personnel-requests/{id}/approve   // تأیید (PATCH)
/personnel-requests/{id}/reject    // رد (PATCH)

/introduction-letters           // لیست معرفی‌نامه‌ها
/introduction-letters/create    // صدور
/introduction-letters/{id}      // جزئیات
/introduction-letters/{id}/cancel      // لغو (PATCH)
/introduction-letters/{id}/mark-as-used   // استفاده شده (PATCH)
/introduction-letters/{id}/print       // چاپ PDF
```

### API (Public):
```php
GET  /api/bale/centers           // لیست مراکز
POST /api/bale/register          // ثبت درخواست
POST /api/bale/check-status      // چک وضعیت
POST /api/bale/letters           // معرفی‌نامه‌های من
```

---

## نصب و اجرا

### 1. Migration
```bash
# Docker
docker-compose exec app php artisan migrate

# Local
php artisan migrate
```

### 2. تخصیص سهمیه به کاربران
```php
// از طریق tinker یا seeder
$user = User::find(1);
$user->quota_total = 50;
$user->save();
```

### 3. فعال‌سازی مراکز
```bash
# مراکز باید در دیتابیس باشند (از seeder قبلی)
```

---

## Workflow کامل

```
1. کاربر ثبت‌نام می‌کنه (بات یا دستی)
   ↓
2. Personnel ایجاد میشه [status: pending]
   ↓
3. مدیر می‌بینه در لیست درخواست‌ها
   ↓
4. مدیر اطلاعات رو چک می‌کنه (دستی)
   ↓
5a. تأیید → [status: approved]
5b. رد → [status: rejected] + دلیل
   ↓
6. اگه تأیید شد → صدور معرفی‌نامه
   ↓
7. IntroductionLetter ایجاد میشه با کد یونیک
   ↓
8. سهمیه مدیر کم میشه (quota_used++)
   ↓
9. کاربر می‌تونه از API چک کنه و معرفی‌نامه رو ببینه
```

---

## تفاوت‌ها با سیستم قبلی

| قبل | فاز 1 |
|-----|-------|
| قرعه‌کشی پیچیده | ❌ حذف شد |
| الگوریتم امتیازدهی | ❌ حذف شد |
| Personnel از قبل در سیستم | ✅ ثبت‌نام خودکار از بات |
| Lottery & LotteryEntry | ❌ حذف شد |
| قانون 3 سال | ❌ فعلا نیست |
| تأیید بعد از قرعه | ✅ تأیید قبل از صدور |
| واحد اتوماتیک تخصیص | ❌ فعلا نیست |

---

## محدودیت‌ها و نکات

### ⚠️ محدودیت‌ها:
- هیچ validation کد پرسنلی با سیستم HR نیست
- استان به صورت دستی انتخاب میشه
- معرفی‌نامه بدون محدودیت زمانی صادر میشه
- هیچ چک سابقه استفاده نیست

### 💡 نکات:
- سهمیه باید توسط ادمین به کاربران داده بشه
- کاربر می‌تونه چند بار درخواست بده (با کد ملی متفاوت)
- معرفی‌نامه قابل لغو است و سهمیه برمی‌گرده
- کد معرفی‌نامه شامل: پیشوند مرکز + سال/ماه + شماره ترتیبی

---

## API Testing

### ثبت درخواست از بات:
```bash
curl -X POST http://localhost:8080/api/bale/register \
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
curl -X POST http://localhost:8080/api/bale/check-status \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "1234567890",
    "identifier_type": "national_code"
  }'
```

---

## فازهای بعدی

### فاز 2 (اختیاری):
- [ ] اتصال به سیستم HR برای validation
- [ ] تخصیص خودکار استان بر اساس محل خدمت
- [ ] نوتیفیکیشن پیامکی
- [ ] محدودیت زمانی برای معرفی‌نامه‌ها
- [ ] گزارش‌گیری پیشرفته

### فاز 3 (آینده):
- [ ] پیاده‌سازی سیستم قرعه‌کشی
- [ ] الگوریتم امتیازدهی
- [ ] قانون 3 سال

---

## تماس و پشتیبانی

در صورت مشکل یا سوال:
- بررسی logs: `storage/logs/laravel.log`
- تست API: استفاده از Postman/Insomnia
- مشاهده migrations: `database/migrations/`

---

**تاریخ به‌روزرسانی:** ۲۰ بهمن ۱۴۰۴
**نسخه:** ۱.۱.۰-phase1
