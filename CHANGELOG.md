# Changelog - سامانه مدیریت مراکز رفاهی بانک ملی ایران

تمامی تغییرات مهم این پروژه در این فایل مستند می‌شود.

---

## [2.0.0] - 2026-02-11

### ✨ امکانات جدید اضافه شده

#### 1. سیستم ثبت‌نام پرسنل با جزئیات همراهان
- **تاریخ:** 2026-02-11
- **Commit:** e17f9ab, d9ca75a, 740460e, 6de7234
- **توضیحات:**
  - سیستم کامل ثبت‌نام پرسنل بدون نیاز به لیست از پیش تعریف شده
  - ثبت اطلاعات کامل سرپرست (کد پرسنلی، نام، کد ملی، تلفن، مرکز مورد نظر)
  - امکان افزودن تا 10 همراه برای هر پرسنل
  - ذخیره اطلاعات جزئی همراهان (نام، نسبت، کد ملی، تاریخ تولد، جنسیت)
  - محاسبه خودکار تعداد کل افراد (family_count)
  - پشتیبانی از منابع ثبت‌نام: دستی، بات بله، وب

#### 2. فیلد اجباری کد پرسنلی
- **تغییر مهم:** فیلد `employee_code` از nullable به required تغییر کرد
- رکوردهای قبلی به صورت خودکار کد موقت (TEMP-{id}) دریافت کردند
- اعتبارسنجی: حداکثر 20 کاراکتر

#### 3. ساختار JSON برای همراهان
```json
{
  "family_members": [
    {
      "full_name": "نام کامل",
      "relation": "همسر|فرزند|پدر|مادر|سایر",
      "national_code": "1234567890",
      "birth_date": "1370/01/01",
      "gender": "male|female"
    }
  ]
}
```

#### 4. راهنمای کامل HTML برای کاربران
- **فایل:** `public/user-guide.html` (43KB)
- **فایل standalone:** `user-guide-standalone.html` (3.76MB)
- راهنمای جامع 10+ بخشی برای اپراتورها
- شامل 10 اسکرین‌شات واقعی از سیستم
- طراحی responsive و RTL
- قابل چاپ به PDF
- نسخه standalone با تصاویر embedded

#### 5. اسکریپت‌های Playwright برای اتوماسیون
- **فایل:** `scripts/take_screenshots.py`
- گرفتن خودکار 10 اسکرین‌شات از سیستم زنده
- پشتیبانی از احراز هویت و navigtion خودکار
- تنظیمات viewport: 1920x1080
- زبان فارسی و RTL support

### 🔄 تغییرات Database

#### Migration: `2026_02_11_000001_add_family_members_to_personnel.php`

**تغییرات جدول `personnel`:**

1. **ستون جدید:** `family_members` (JSON, nullable)
   - ذخیره اطلاعات جزئی همراهان
   - قابل جستجو و فیلتر
   - Comment: "اطلاعات جزئی همراهان"

2. **تغییر ستون:** `employee_code`
   - از `nullable()` به `required` تغییر کرد
   - رکوردهای قبلی: TEMP-{id}
   - حداکثر 20 کاراکتر

3. **بدون تغییر:** `family_count`
   - همچنان وجود دارد (برای سرعت query)
   - به صورت خودکار از `family_members` محاسبه می‌شود

### 🛠️ تغییرات Model

#### `app/Models/Personnel.php`

**متدهای جدید:**
```php
public function getFamilyMembersCount(): int
public function getTotalPersonsCount(): int
public function hasFamilyMembers(): bool
```

**ثوابت جدید:**
```php
const RELATION_SPOUSE = 'همسر';
const RELATION_CHILD = 'فرزند';
const RELATION_FATHER = 'پدر';
const RELATION_MOTHER = 'مادر';
const RELATION_OTHER = 'سایر';
```

**Boot Event:**
- محاسبه خودکار `family_count` هنگام ذخیره
- بروزرسانی خودکار بدون نیاز به دخالت developer

**Casts:**
- `family_members` => `array`
- امکان دسترسی راحت به داده‌های JSON

### 🎨 تغییرات Frontend

#### `resources/views/personnel-requests/create.blade.php`

**بخش جدید: افزودن همراهان**
- دکمه "افزودن همراه" با JavaScript
- فرم داینامیک برای هر همراه
- فیلدها: نام، نسبت، کد ملی، تاریخ تولد، جنسیت
- دکمه حذف برای هر ردیف
- حفظ داده‌ها در صورت خطای validation
- UI/UX بهبود یافته با Bootstrap 5

**اعتبارسنجی سمت کلاینت:**
- کد ملی: دقیقاً 10 رقم
- فیلدهای اجباری: نام، نسبت، کد ملی، جنسیت
- حداکثر 10 همراه

#### `resources/views/personnel-requests/show.blade.php`

**بخش جدید: نمایش همراهان**
- جدول کامل اطلاعات همراهان
- نمایش نسبت با badge
- آیکون جنسیت
- جمع کل افراد (1 سرپرست + n همراه)
- Alert box برای تأکید

#### `resources/views/personnel-requests/index.blade.php`

**تغییرات:**
- نمایش تعداد کل افراد در لیست
- Badge برای منبع ثبت‌نام (دستی، بله، وب)
- فیلتر بر اساس منبع

### 🔌 تغییرات API

#### `app/Http/Controllers/Api/PersonnelRequestController.php`

**Endpoint:** `POST /api/personnel-requests/register`

**Validation Rules:**
```php
'employee_code' => 'required|string|max:20',
'national_code' => 'required|string|size:10|unique:personnel,national_code',
'family_members' => 'nullable|array|max:10',
'family_members.*.full_name' => 'required|string|max:255',
'family_members.*.relation' => 'required|in:همسر,فرزند,پدر,مادر,سایر',
'family_members.*.national_code' => 'required|string|size:10',
'family_members.*.birth_date' => 'nullable|string|max:10',
'family_members.*.gender' => 'required|in:male,female',
```

**Response جدید:**
```json
{
  "success": true,
  "message": "درخواست شما با موفقیت ثبت شد",
  "data": {
    "tracking_code": "REQ-0211-0001",
    "total_persons": 4,
    "family_members_count": 3,
    "status": "در انتظار بررسی"
  }
}
```

**سایر Endpoints:**
- `POST /api/personnel-requests/check-status` - تغییرات در response
- `GET /api/personnel-requests/letters` - بدون تغییر
- `GET /api/centers` - بدون تغییر

#### پیام‌های خطای فارسی
```php
'employee_code.required' => 'کد پرسنلی الزامی است',
'national_code.unique' => 'این کد ملی قبلاً ثبت شده است',
'family_members.*.national_code.size' => 'کد ملی همراه باید 10 رقم باشد',
'family_members.*.relation.in' => 'نسبت وارد شده معتبر نیست',
```

### 📋 تغییرات Controller

#### `app/Http/Controllers/PersonnelRequestController.php`

**متد `store()`:**
- اعتبارسنجی `employee_code` اجباری
- اعتبارسنجی آرایه `family_members`
- اعتبارسنجی تک‌تک فیلدهای همراهان
- حداکثر 10 همراه

**متد `update()`:**
- امکان ویرایش همراهان
- حفظ ساختار JSON
- Validation مشابه store

### 🚀 تغییرات Routes

#### `routes/web.php`

**Route جدید:**
```php
Route::get('/user-guide', function () {
    return response()->file(public_path('user-guide.html'));
})->name('user-guide');
```

**دسترسی:**
- بدون نیاز به authentication
- قابل دسترسی برای همه
- URL: `/user-guide` یا `/user-guide.html`

### 📚 مستندات

#### OpenSpec Specifications

**فایل:** `openspec/changes/family-members-system/spec.md`

محتوا:
- معماری کامل سیستم همراهان
- ساختار JSON با مثال‌های واقعی
- تمام validation rules
- نمونه request/response API
- نمونه کدهای frontend
- Database schema
- Migration instructions

#### راهنمای اسکرین‌شات

**فایل:** `SCREENSHOT_GUIDE.md`

محتوا:
- لیست 10 اسکرین‌شات مورد نیاز
- نام فایل‌ها و مسیرها
- توضیحات محتوای هر اسکرین‌شات
- راهنمای بهینه‌سازی تصاویر
- Checklist قبل از انتشار

#### راهنمای کاربری HTML

**فایل:** `public/user-guide.html`

بخش‌های راهنما:
1. ورود به سامانه
2. داشبورد اصلی
3. ثبت درخواست پرسنل
4. مدیریت درخواست‌ها
5. صدور معرفی‌نامه
6. مدیریت سهمیه کاربران
7. کنترل ثبت‌نام
8. ثبت درخواست از بات بله
9. API Endpoints
10. سوالات متداول (FAQ)
11. تماس با پشتیبانی

### 🧪 Testing

**تست‌های مورد نیاز (پیشنهادی):**

```php
// tests/Feature/PersonnelRequestTest.php
test('can create personnel request with family members')
test('validates employee code is required')
test('validates national code is unique')
test('validates maximum 10 family members')
test('validates family member national code format')
test('calculates total persons count correctly')
test('stores family members as JSON')
```

### 🔧 اسکریپت‌های اتوماسیون

#### `scripts/take_screenshots.py`
- Python 3.14 compatible
- Playwright automation
- گرفتن 10 اسکرین‌شات از:
  1. Login page
  2. Dashboard
  3. Personnel requests list
  4. Request form (supervisor section)
  5. Request form (family section)
  6. Request details page
  7. Introduction letter form
  8. Issued letter
  9. Quota management
  10. Registration control

#### `scripts/create_standalone_guide.py`
- تبدیل user-guide.html به standalone
- Embed کردن تصاویر به صورت base64
- خروجی: فایل 3.76MB خودکفا

### 📦 Dependencies

**جدید:**
```json
{
  "devDependencies": {
    "@playwright/test": "latest",
    "playwright": "latest"
  }
}
```

**Python:**
```
playwright==1.56.0
pyee==13.0.0
greenlet==3.2.4
```

### 🐛 Bug Fixes

1. **404 Error on user-guide.html**
   - مشکل: فایل HTML در public بود ولی Laravel route نداشت
   - حل: اضافه کردن route در `routes/web.php`

2. **Employee Code Nullable**
   - مشکل: کد پرسنلی اختیاری بود
   - حل: تبدیل به required با migration

3. **Family Count Not Auto-Updated**
   - مشکل: تعداد همراهان دستی محاسبه می‌شد
   - حل: اضافه کردن boot event در model

### 🔐 Security

**اعتبارسنجی‌های امنیتی:**
- Validation کد ملی: دقیقاً 10 رقم
- Unique constraint برای national_code
- محدودیت 10 همراه (جلوگیری از DOS)
- Sanitization ورودی‌های کاربر
- CSRF protection در فرم‌ها

### 📊 Database Changes Summary

```sql
-- New column
ALTER TABLE personnel ADD COLUMN family_members JSON NULL
  COMMENT 'اطلاعات جزئی همراهان';

-- Update existing records
UPDATE personnel
SET employee_code = CONCAT('TEMP-', id)
WHERE employee_code IS NULL OR employee_code = '';

-- Make column required
ALTER TABLE personnel MODIFY COLUMN employee_code VARCHAR(20) NOT NULL;
```

### 🌐 Localization

**پیام‌های فارسی:**
- تمام پیام‌های validation به فارسی
- راهنمای کامل به زبان فارسی
- UI labels و placeholders فارسی
- Error messages فارسی

### 📁 File Structure Changes

```
welfare-V2/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Api/PersonnelRequestController.php (modified)
│   │       └── PersonnelRequestController.php (modified)
│   └── Models/
│       └── Personnel.php (modified)
├── database/
│   └── migrations/
│       └── 2026_02_11_000001_add_family_members_to_personnel.php (new)
├── openspec/
│   └── changes/
│       └── family-members-system/
│           └── spec.md (new)
├── public/
│   ├── screenshots/ (new - 10 images, 2.7MB)
│   │   ├── screenshot-1-login.png
│   │   ├── screenshot-2-dashboard.png
│   │   └── ... (8 more)
│   └── user-guide.html (modified)
├── resources/
│   └── views/
│       └── personnel-requests/
│           ├── create.blade.php (major rewrite)
│           ├── show.blade.php (modified)
│           └── index.blade.php (modified)
├── routes/
│   └── web.php (modified - new route)
├── scripts/ (new)
│   ├── take_screenshots.py
│   ├── take-screenshots.js
│   └── create_standalone_guide.py
├── CHANGELOG.md (new)
├── SCREENSHOT_GUIDE.md (new)
├── user-guide-standalone.html (new - 3.76MB)
└── package.json (new)
```

### 🚀 Deployment

**مراحل deploy:**

1. Pull latest code:
```bash
git pull origin main
```

2. Run migrations:
```bash
php artisan migrate
```

3. Clear caches:
```bash
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

4. Restart services (if needed):
```bash
docker-compose restart
```

### 📝 Git Commits

**تاریخچه commit ها:**

1. **e17f9ab** - Add family members system to personnel requests
   - Migration for family_members column
   - Model updates with helper methods
   - Controller validation
   - Frontend forms

2. **d9ca75a** - Add comprehensive HTML user guide
   - Created user-guide.html
   - Added SCREENSHOT_GUIDE.md
   - Documentation for operators

3. **740460e** - Add screenshots to user guide using Playwright
   - Playwright automation script
   - 10 screenshots captured
   - Images embedded in HTML

4. **6de7234** - Add route for user guide HTML page
   - Public route for /user-guide
   - No authentication required

### 🔮 Future Improvements

**پیشنهادات برای آینده:**

1. **Validation پیشرفته‌تر:**
   - اعتبارسنجی واقعی کد ملی (الگوریتم checksum)
   - تشخیص کد ملی تکراری در همراهان
   - اعتبارسنجی تاریخ تولد (فرمت شمسی)

2. **File Upload:**
   - آپلود عکس پرسنل
   - آپلود مدارک همراهان
   - ذخیره در storage

3. **Export/Import:**
   - Export لیست درخواست‌ها به Excel
   - Import انبوه پرسنل از CSV
   - PDF generation برای معرفی‌نامه‌ها

4. **Notifications:**
   - اطلاع‌رسانی SMS برای تأیید/رد
   - ایمیل برای صدور معرفی‌نامه
   - نوتیفیکیشن در بات بله

5. **Analytics:**
   - گزارش آماری ثبت‌نام‌ها
   - نمودار روند درخواست‌ها
   - تحلیل محبوب‌ترین مراکز

6. **Performance:**
   - Indexing روی family_members (JSON search)
   - Caching برای queries پرتکرار
   - Lazy loading برای جداول بزرگ

### 📞 Support

**در صورت بروز مشکل:**

1. بررسی logs:
```bash
tail -f storage/logs/laravel.log
```

2. چک کردن database connection:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

3. تست migration:
```bash
php artisan migrate:status
```

---

## نکات مهم

### ⚠️ Breaking Changes

1. **فیلد employee_code اجباری شده است**
   - تمام رکوردهای قبلی کد موقت دریافت کرده‌اند
   - برای ثبت‌نام جدید باید کد پرسنلی وارد شود

2. **ساختار family_members تغییر کرده**
   - قبلاً: فقط تعداد (family_count)
   - حالا: اطلاعات کامل به صورت JSON

### ✅ Backward Compatibility

- رکوردهای قدیمی با family_members = null کار می‌کنند
- متدهای قدیمی Personnel model حفظ شده‌اند
- API endpoints قبلی همچنان کار می‌کنند

### 🎯 Best Practices

1. همیشه از متد `getTotalPersonsCount()` استفاده کنید
2. برای افزودن همراه، از validation rules استفاده کنید
3. قبل از deploy، migration را تست کنید
4. Cache را بعد از deploy پاک کنید

---

**آخرین بروزرسانی:** 2026-02-11
**نسخه:** 2.0.0
**توسعه‌دهنده:** sedalcrazy-create
**مشارکت:** Claude Sonnet 4.5
