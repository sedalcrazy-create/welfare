# Phase 1: Introduction Letter System with User Quota
## سیستم صدور معرفی‌نامه با سهمیه کاربری

**Version:** 1.0.0
**Date:** 2026-02-12
**Status:** Ready for Implementation
**Priority:** HIGH
**Estimated Effort:** 3-4 weeks

---

## 📋 Overview

سیستم صدور معرفی‌نامه برای مراکز رفاهی بانک ملی ایران بدون قرعه‌کشی. در این فاز، هر کاربر (اپراتور) سهمیه مشخصی برای هر مرکز دارد و می‌تواند برای دوره‌های مشخص، اطلاعات پرسنل و همراهان را ثبت کرده و پس از تأیید ادمین، معرفی‌نامه صادر کند.

---

## 🎯 Why - چرا

### مشکل فعلی:
- ❌ صدور معرفی‌نامه از طریق اتوماسیون اداری (پراکندگی اطلاعات)
- ❌ عدم کنترل سهمیه به صورت متمرکز
- ❌ عدم امکان گزارش‌گیری یکپارچه
- ❌ فرآیند دستی و زمان‌بر
- ❌ عدم یکپارچگی با بات بله

### راه‌حل:
- ✅ سیستم تحت وب متمرکز
- ✅ مدیریت سهمیه کاربری (User-based Quota)
- ✅ ثبت اطلاعات پرسنل + همراهان با جزئیات
- ✅ گردش کار تأیید توسط ادمین
- ✅ صدور خودکار معرفی‌نامه PDF
- ✅ یکپارچگی کامل با بات بله

---

## 🔄 What Changes - چه تغییراتی

### New Features:

#### 1. User Quota Management (مدیریت سهمیه کاربری)
- ادمین سهمیه تخصیص می‌دهد به هر کاربر برای هر مرکز
- نمایش سهمیه کل، استفاده شده، باقیمانده
- امکان افزایش/کاهش/ریست سهمیه

#### 2. Personnel Request System (سیستم ثبت درخواست پرسنل)
- ثبت اطلاعات سرپرست (کد پرسنلی، نام، کد ملی، موبایل)
- انتخاب مرکز و دوره اقامت
- ثبت اطلاعات همراهان (حداکثر 10 نفر) با:
  - نام کامل
  - نسبت (همسر، فرزند، پدر، مادر، سایر)
  - کد ملی
  - تاریخ تولد (اختیاری)
  - جنسیت
- تولید خودکار tracking code
- بررسی خودکار سهمیه قبل از ثبت

#### 3. Admin Approval Workflow (گردش کار تأیید)
- لیست درخواست‌های در انتظار
- نمایش جزئیات کامل درخواست
- تأیید یا رد با ذکر دلیل
- اطلاع‌رسانی به متقاضی

#### 4. Introduction Letter Issuance (صدور معرفی‌نامه)
- تولید خودکار کد معرفی‌نامه (یکتا)
- صدور معرفی‌نامه برای درخواست‌های تأیید شده
- کسر خودکار سهمیه
- تولید PDF قابل چاپ
- امکان لغو معرفی‌نامه (برگشت سهمیه)

#### 5. Bale Bot Integration (یکپارچگی بات بله)
- ثبت‌نام مستقیم از بات بله
- انتخاب مرکز و دوره با inline keyboards
- ثبت اطلاعات همراهان به صورت تعاملی
- Mobile Number Normalizer (پشتیبانی از فرمت‌های مختلف)
- پیگیری وضعیت درخواست
- دریافت معرفی‌نامه PDF

---

## 🏗️ System Architecture

### Entity Relationship:

```
User (کاربر/اپراتور)
├─ has many UserCenterQuota
├─ issues many IntroductionLetter
└─ creates many Personnel

UserCenterQuota (سهمیه کاربر-مرکز)
├─ belongs to User
├─ belongs to Center
├─ quota_total (کل)
├─ quota_used (استفاده شده)
└─ quota_remaining (باقیمانده - generated)

Center (مرکز رفاهی)
├─ has many Period
├─ has many Personnel (via preferred_center_id)
└─ has many IntroductionLetter

Period (دوره اقامت)
├─ belongs to Center
├─ start_date, end_date
├─ capacity
├─ status (draft/open/closed)
└─ has many Personnel (via preferred_period_id)

Personnel (درخواست پرسنل)
├─ employee_code (کد پرسنلی) [REQUIRED]
├─ full_name, national_code, phone
├─ preferred_center_id [REQUIRED]
├─ preferred_period_id [REQUIRED] ⭐ NEW
├─ family_members (JSON) [اطلاعات همراهان]
├─ family_count (خودکار محاسبه می‌شود)
├─ status (pending/approved/rejected)
├─ registration_source (web/bale_bot)
├─ tracking_code (کد پیگیری یکتا)
└─ has one IntroductionLetter

IntroductionLetter (معرفی‌نامه)
├─ letter_code (کد یکتا) [MAS-0501-0001]
├─ belongs to Personnel
├─ belongs to Center
├─ belongs to Period ⭐ NEW
├─ issued_by_user_id (صادرکننده)
├─ family_count
├─ status (active/used/cancelled/expired)
└─ timestamps
```

---

## 🗄️ Database Schema Changes

### Migration 1: Add period_id to personnel

**File:** `database/migrations/2026_02_12_000001_add_period_to_personnel.php`

```php
public function up(): void
{
    Schema::table('personnel', function (Blueprint $table) {
        $table->foreignId('preferred_period_id')
            ->nullable()
            ->after('preferred_center_id')
            ->constrained('periods')
            ->nullOnDelete()
            ->comment('دوره مورد نظر برای اقامت');

        $table->index('preferred_period_id');
        $table->index(['preferred_center_id', 'preferred_period_id']);
    });
}

public function down(): void
{
    Schema::table('personnel', function (Blueprint $table) {
        $table->dropForeign(['preferred_period_id']);
        $table->dropIndex(['preferred_center_id', 'preferred_period_id']);
        $table->dropIndex(['preferred_period_id']);
        $table->dropColumn('preferred_period_id');
    });
}
```

### Migration 2: Add period_id to introduction_letters

**File:** `database/migrations/2026_02_12_000002_add_period_to_introduction_letters.php`

```php
public function up(): void
{
    Schema::table('introduction_letters', function (Blueprint $table) {
        $table->foreignId('period_id')
            ->nullable()
            ->after('center_id')
            ->constrained('periods')
            ->restrictOnDelete()
            ->comment('دوره اقامت');

        $table->index(['center_id', 'period_id']);
        $table->index(['period_id', 'status']);
    });
}

public function down(): void
{
    Schema::table('introduction_letters', function (Blueprint $table) {
        $table->dropForeign(['period_id']);
        $table->dropIndex(['center_id', 'period_id']);
        $table->dropIndex(['period_id', 'status']);
        $table->dropColumn('period_id');
    });
}
```

### Existing Tables (Already Created):

#### user_center_quotas
```sql
CREATE TABLE user_center_quotas (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    center_id BIGINT NOT NULL REFERENCES centers(id) ON DELETE CASCADE,
    quota_total INT DEFAULT 0 COMMENT 'تعداد کل سهمیه',
    quota_used INT DEFAULT 0 COMMENT 'تعداد استفاده شده',
    quota_remaining INT GENERATED ALWAYS AS (quota_total - quota_used) STORED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(user_id, center_id)
);
```

#### personnel (with family_members)
```sql
-- Fields relevant to Phase 1:
employee_code VARCHAR(20) NOT NULL
national_code VARCHAR(10) UNIQUE NOT NULL
full_name VARCHAR(255) NOT NULL
phone VARCHAR(20) NOT NULL
preferred_center_id BIGINT REFERENCES centers(id)
preferred_period_id BIGINT REFERENCES periods(id) -- ⭐ NEW
family_members JSON -- ⭐ [{"full_name": "...", "relation": "...", ...}]
family_count INT -- Auto-calculated
status ENUM('pending', 'approved', 'rejected')
registration_source ENUM('web', 'bale_bot', 'manual')
tracking_code VARCHAR(20) UNIQUE
bale_user_id VARCHAR(100) UNIQUE NULLABLE
```

#### introduction_letters
```sql
CREATE TABLE introduction_letters (
    id BIGSERIAL PRIMARY KEY,
    letter_code VARCHAR(30) UNIQUE NOT NULL,
    personnel_id BIGINT REFERENCES personnel(id) ON DELETE RESTRICT,
    center_id BIGINT REFERENCES centers(id) ON DELETE RESTRICT,
    period_id BIGINT REFERENCES periods(id) ON DELETE RESTRICT, -- ⭐ NEW
    issued_by_user_id BIGINT REFERENCES users(id) ON DELETE RESTRICT,
    family_count INT DEFAULT 1,
    valid_from VARCHAR(10), -- Jalali date
    valid_until VARCHAR(10), -- Jalali date
    issued_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP,
    status ENUM('active', 'used', 'cancelled', 'expired') DEFAULT 'active',
    cancellation_reason TEXT,
    cancelled_by_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    cancelled_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🛠️ Technical Implementation

### Controllers

```
app/Http/Controllers/
├── Admin/
│   ├── QuotaController.php
│   │   ├── index(User $user)              // لیست سهمیه‌های یک کاربر
│   │   ├── allocate(AllocateQuotaRequest) // تخصیص سهمیه
│   │   ├── update(UserCenterQuota $quota) // افزایش/کاهش
│   │   └── reset(UserCenterQuota $quota)  // ریست استفاده شده
│   │
│   └── PersonnelApprovalController.php
│       ├── pending()                      // لیست در انتظار
│       ├── approve(Personnel $personnel)  // تأیید
│       └── reject(RejectRequest)          // رد با دلیل
│
├── PersonnelRequestController.php
│   ├── index()                            // لیست درخواست‌ها
│   ├── create()                           // فرم ثبت
│   ├── store(StorePersonnelRequest)       // ذخیره + بررسی سهمیه
│   ├── show(Personnel $personnel)         // نمایش جزئیات
│   ├── edit(Personnel $personnel)         // فرم ویرایش
│   ├── update(UpdatePersonnelRequest)     // بروزرسانی
│   └── destroy(Personnel $personnel)      // حذف
│
├── IntroductionLetterController.php
│   ├── index()                            // لیست معرفی‌نامه‌ها
│   ├── create(Personnel $personnel)       // فرم صدور
│   ├── store(IssueLetterRequest)          // صدور + کسر سهمیه
│   ├── show(IntroductionLetter $letter)   // نمایش
│   ├── pdf(IntroductionLetter $letter)    // دانلود PDF
│   └── cancel(CancelLetterRequest)        // لغو + برگشت سهمیه
│
└── Api/
    ├── PersonnelRequestController.php
    │   ├── register(RegisterRequest)      // ثبت از بات بله
    │   ├── checkStatus(Request)           // پیگیری با tracking_code
    │   └── getLetters(Request)            // معرفی‌نامه‌های کاربر
    │
    ├── CenterController.php
    │   └── index()                        // لیست مراکز
    │
    └── PeriodController.php
        └── index(Request)                 // لیست دوره‌ها (فیلتر: مرکز، وضعیت)
```

### Services

```
app/Services/
├── QuotaService.php
│   ├── checkQuota(User $user, Center $center): bool
│   ├── allocateQuota(User $user, Center $center, int $amount): void
│   ├── consumeQuota(User $user, Center $center): void
│   ├── refundQuota(User $user, Center $center): void
│   └── getQuotaSummary(User $user): Collection
│
├── PersonnelService.php
│   ├── createRequest(array $data, User $user): Personnel
│   ├── updateRequest(Personnel $personnel, array $data): void
│   ├── approve(Personnel $personnel, User $admin): void
│   └── reject(Personnel $personnel, User $admin, string $reason): void
│
├── LetterService.php
│   ├── generateCode(Center $center, Period $period): string
│   ├── issue(Personnel $personnel, User $issuer): IntroductionLetter
│   ├── cancel(IntroductionLetter $letter, User $user, string $reason): void
│   └── generatePDF(IntroductionLetter $letter): Response
│
└── BaleBot/
    ├── MobileNumberNormalizer.php
    │   ├── normalize(string $mobile): array
    │   └── fromBaleContact(array $contact): ?string
    │
    ├── KeyboardBuilder.php
    │   ├── centerSelectionKeyboard(Collection $centers)
    │   ├── periodSelectionKeyboard(Collection $periods)
    │   ├── relationKeyboard()
    │   ├── genderKeyboard()
    │   ├── confirmationKeyboard()
    │   └── mainMenuKeyboard()
    │
    └── StateManager.php
        ├── setState(int $userId, string $step, array $data): void
        ├── getState(int $userId): ?array
        ├── clearState(int $userId): void
        └── updateData(int $userId, string $key, mixed $value): void
```

### Form Requests

```
app/Http/Requests/
├── AllocateQuotaRequest.php
│   Rules: user_id, center_id, quota_total (required, integer, min:0)
│
├── StorePersonnelRequest.php
│   Rules:
│   - employee_code (required, string, max:20)
│   - full_name (required, string, max:255)
│   - national_code (required, string, size:10, unique)
│   - phone (required, string, max:20)
│   - preferred_center_id (required, exists:centers)
│   - preferred_period_id (required, exists:periods)
│   - family_members (nullable, array, max:10)
│   - family_members.*.full_name (required, string)
│   - family_members.*.relation (required, in:همسر,فرزند,پدر,مادر,سایر)
│   - family_members.*.national_code (required, string, size:10)
│   - family_members.*.gender (required, in:male,female)
│   - family_members.*.birth_date (nullable, string, max:10)
│
├── UpdatePersonnelRequest.php
│   Same as Store but national_code unique:personnel,national_code,{id}
│
├── IssueLetterRequest.php
│   Rules: personnel_id (required, exists), notes (nullable, string)
│
└── CancelLetterRequest.php
    Rules: cancellation_reason (required, string, min:10)
```

### Policies

```
app/Policies/
├── UserCenterQuotaPolicy.php
│   - viewAny: admin|super_admin
│   - allocate: admin|super_admin
│   - update: admin|super_admin
│   - reset: super_admin
│
├── PersonnelPolicy.php (UPDATE EXISTING)
│   - viewAny: operator|admin|super_admin
│   - view: owner or admin|super_admin
│   - create: operator|admin|super_admin
│   - update: owner or admin|super_admin (if pending)
│   - delete: owner or admin|super_admin (if pending)
│   - approve: admin|super_admin
│   - reject: admin|super_admin
│
└── IntroductionLetterPolicy.php
    - viewAny: operator|admin|super_admin
    - view: issuer or admin|super_admin
    - issue: operator|admin|super_admin (with quota check)
    - cancel: issuer or admin|super_admin
```

---

## 🌐 API Endpoints

### Public API (for Bale Bot)

```http
# Centers
GET /api/centers
Response: [
  {
    "id": 1,
    "name": "زائرسرای مشهد",
    "slug": "mashhad",
    "city": "مشهد",
    "type": "religious",
    "stay_duration": 5,
    "total_units": 227,
    "total_beds": 1029
  },
  ...
]

# Periods
GET /api/periods?center_id=1&status=open
Response: [
  {
    "id": 5,
    "center_id": 1,
    "center_name": "زائرسرای مشهد",
    "title": "نوروز 1405",
    "start_date": "1405-01-01",
    "end_date": "1405-01-05",
    "capacity": 200,
    "registered_count": 50,
    "remaining_capacity": 150,
    "status": "open"
  },
  ...
]

# Personnel Request Registration
POST /api/personnel-requests/register
Headers: Content-Type: application/json
Body: {
  "employee_code": "12345",
  "full_name": "علی احمدی",
  "national_code": "1234567890",
  "phone": "09123456789",
  "preferred_center_id": 1,
  "preferred_period_id": 5,
  "bale_user_id": "123456789",
  "family_members": [
    {
      "full_name": "فاطمه محمدی",
      "relation": "همسر",
      "national_code": "0987654321",
      "gender": "female",
      "birth_date": "1370/01/01"
    }
  ]
}
Response: {
  "success": true,
  "message": "درخواست با موفقیت ثبت شد",
  "data": {
    "tracking_code": "REQ-0412-0001",
    "total_persons": 2,
    "status": "pending"
  }
}

# Check Status
POST /api/personnel-requests/check-status
Body: {
  "tracking_code": "REQ-0412-0001"
}
Response: {
  "success": true,
  "data": {
    "tracking_code": "REQ-0412-0001",
    "full_name": "علی احمدی",
    "center": "زائرسرای مشهد",
    "period": "نوروز 1405",
    "total_persons": 2,
    "status": "approved",
    "status_label": "تأیید شده",
    "letter": {
      "letter_code": "MAS-0501-0001",
      "issued_at": "1404/12/16 10:00"
    }
  }
}

# Get Letters
GET /api/personnel-requests/letters?national_code=1234567890
Response: {
  "success": true,
  "data": [
    {
      "letter_code": "MAS-0501-0001",
      "center": "زائرسرای مشهد",
      "period": "نوروز 1405",
      "family_count": 2,
      "issued_at": "1404/12/16 10:00",
      "status": "active"
    }
  ]
}
```

### Authenticated Web API

```http
# Quota Management (Admin)
GET /admin/users/{user}/quotas
POST /admin/users/{user}/quotas/allocate
PATCH /admin/quotas/{quota}
POST /admin/quotas/{quota}/reset

# Personnel Requests
GET /personnel-requests
POST /personnel-requests
GET /personnel-requests/{id}
PATCH /personnel-requests/{id}
DELETE /personnel-requests/{id}

# Admin Approval
GET /admin/personnel-requests/pending
POST /admin/personnel-requests/{id}/approve
POST /admin/personnel-requests/{id}/reject

# Introduction Letters
GET /letters
POST /letters/issue
GET /letters/{id}
GET /letters/{id}/pdf
POST /letters/{id}/cancel
```

---

## 📱 Bale Bot User Flow

### Complete Flow with Keyboards:

1. **Start** → Main menu (inline buttons)
2. **Select Center** → Cards with icons (inline buttons)
3. **Select Period** → Available periods list (inline buttons)
4. **Supervisor Info** → Text input with validation
5. **Mobile Number** → Request contact button OR manual input
   - Supports Persian/English numbers
   - Supports all formats (+98, spaces, dashes)
6. **Family Count** → Number buttons (0-10)
7. **Each Family Member**:
   - Name → Text input
   - Relation → Inline buttons (👰همسر, 👶فرزند, etc.)
   - National Code → Text input with validation
   - Gender → Inline buttons (👩زن, 👨مرد)
   - Birth Date → Optional calendar picker
8. **Summary & Confirmation** → Review + Edit/Confirm buttons
9. **Submission** → Tracking code + Status
10. **Tracking** → Check status anytime
11. **PDF Download** → Get letter when approved

### Key Features:
- ✅ Inline keyboards for better UX
- ✅ Request contact button for mobile
- ✅ Mobile number normalizer (all formats)
- ✅ State management (2 hour cache)
- ✅ Validation at each step
- ✅ Edit capability at any step
- ✅ Persian error messages
- ✅ Emoji for better visual

---

## 🎨 Frontend Views

### Admin Panel Views:

```
resources/views/
├── admin/
│   ├── quotas/
│   │   ├── index.blade.php          // لیست کاربران + سهمیه‌ها
│   │   └── allocate-modal.blade.php // Modal تخصیص سهمیه
│   │
│   └── personnel-approvals/
│       ├── pending.blade.php        // لیست درخواست‌های pending
│       └── show.blade.php           // جزئیات + تأیید/رد
│
├── personnel-requests/
│   ├── index.blade.php              // لیست (با فیلتر)
│   ├── create.blade.php             // فرم ثبت (با JS برای همراهان)
│   ├── show.blade.php               // نمایش جزئیات
│   └── edit.blade.php               // ویرایش
│
└── letters/
    ├── index.blade.php              // لیست معرفی‌نامه‌ها
    ├── issue.blade.php              // فرم صدور
    ├── show.blade.php               // نمایش
    └── pdf.blade.php                // Template PDF
```

### JavaScript Components:

```javascript
// resources/js/components/
├── FamilyMemberManager.js           // افزودن/حذف همراهان
├── QuotaDisplay.js                  // نمایش سهمیه realtime
├── PeriodSelector.js                // انتخاب دوره بر اساس مرکز
└── NationalCodeValidator.js         // اعتبارسنجی کد ملی
```

---

## ✅ Acceptance Criteria

### User Quota Management
- [ ] ادمین می‌تواند سهمیه تخصیص دهد به کاربر برای هر مرکز
- [ ] ادمین می‌تواند سهمیه را افزایش/کاهش دهد
- [ ] ادمین می‌تواند سهمیه استفاده شده را ریست کند
- [ ] سهمیه باقیمانده به صورت خودکار محاسبه شود
- [ ] هنگام تخصیص سهمیه، validation انجام شود (مقدار >= 0)

### Personnel Request (Web Panel)
- [ ] اپراتور می‌تواند درخواست جدید ثبت کند
- [ ] انتخاب مرکز و دوره اجباری باشد
- [ ] فقط دوره‌های باز (open) قابل انتخاب باشند
- [ ] اطلاعات سرپرست کامل باشد (با کد پرسنلی اجباری)
- [ ] حداکثر 10 همراه قابل افزودن باشد
- [ ] هر همراه شامل: نام، نسبت، کد ملی، جنسیت، تاریخ تولد (اختیاری)
- [ ] کد ملی یکتا باشد (unique validation)
- [ ] تعداد کل افراد خودکار محاسبه شود
- [ ] قبل از ثبت، سهمیه کاربر چک شود
- [ ] tracking code یکتا تولید شود
- [ ] وضعیت اولیه pending باشد

### Personnel Request (Bale Bot)
- [ ] پرسنل از بات بله ثبت‌نام کند
- [ ] مراکز به صورت کارت با آیکون نمایش داده شوند
- [ ] دوره‌های باز فیلتر شده نمایش داده شوند
- [ ] دکمه "ارسال شماره موبایل" کار کند
- [ ] اگر کاربر تایپ کرد، Mobile Normalizer اعمال شود
- [ ] فرمت‌های مختلف موبایل (فارسی، انگلیسی، با فاصله، +98) قبول شوند
- [ ] نسبت همراهان با دکمه emoji انتخاب شوند
- [ ] خلاصه نهایی قبل از ارسال نمایش داده شود
- [ ] امکان ویرایش قبل از ارسال وجود داشته باشد
- [ ] tracking code به کاربر ارسال شود
- [ ] State تا 2 ساعت حفظ شود

### Admin Approval
- [ ] ادمین لیست درخواست‌های pending را ببیند
- [ ] جزئیات کامل درخواست (سرپرست + همراهان) نمایش داده شود
- [ ] ادمین بتواند تأیید کند (status → approved)
- [ ] ادمین بتواند رد کند با ذکر دلیل (status → rejected)
- [ ] پس از رد، سهمیه برگردد (refund quota)

### Introduction Letter Issuance
- [ ] فقط درخواست‌های approved قابل صدور باشند
- [ ] قبل از صدور، سهمیه چک شود
- [ ] کد معرفی‌نامه یکتا تولید شود با فرمت: {CENTER}-{YYمم}-{NUM}
- [ ] معرفی‌نامه ذخیره شود با وضعیت active
- [ ] سهمیه کاربر کم شود (quota_used++)
- [ ] PDF با کیفیت تولید شود شامل:
  - مشخصات سرپرست
  - جدول همراهان
  - اطلاعات مرکز و دوره
  - QR code
- [ ] امکان لغو معرفی‌نامه وجود داشته باشد
- [ ] پس از لغو، سهمیه برگردد

### Bale Bot Tracking
- [ ] کاربر با tracking code پیگیری کند
- [ ] وضعیت فعلی درخواست نمایش داده شود
- [ ] اگر approved، اطلاعات معرفی‌نامه نمایش داده شود
- [ ] امکان دانلود PDF معرفی‌نامه وجود داشته باشد

### Reports & Monitoring
- [ ] ادمین تعداد کل درخواست‌ها را ببیند
- [ ] ادمین درخواست‌های pending را ببیند
- [ ] ادمین معرفی‌نامه‌های صادر شده را ببیند
- [ ] گزارش مصرف سهمیه هر کاربر

---

## 🧪 Testing Strategy

### Unit Tests

```php
tests/Unit/Services/
├── QuotaServiceTest.php
│   - test_allocate_quota
│   - test_consume_quota
│   - test_refund_quota
│   - test_check_quota_insufficient
│
├── MobileNumberNormalizerTest.php
│   - test_normalize_standard_format
│   - test_normalize_persian_numbers
│   - test_normalize_with_spaces
│   - test_normalize_with_country_code
│   - test_normalize_without_leading_zero
│   - test_reject_invalid_format
│
└── LetterServiceTest.php
    - test_generate_unique_code
    - test_issue_letter_success
    - test_issue_letter_insufficient_quota
    - test_cancel_letter_refunds_quota
```

### Feature Tests

```php
tests/Feature/
├── QuotaManagementTest.php
│   - test_admin_can_allocate_quota
│   - test_admin_can_view_quotas
│   - test_operator_cannot_allocate_quota
│
├── PersonnelRequestTest.php
│   - test_operator_can_create_request
│   - test_create_request_validates_quota
│   - test_create_request_with_family_members
│   - test_create_request_generates_tracking_code
│   - test_cannot_create_without_period
│   - test_family_members_count_auto_calculated
│
├── ApprovalWorkflowTest.php
│   - test_admin_can_approve_request
│   - test_admin_can_reject_request
│   - test_rejection_refunds_quota
│   - test_operator_cannot_approve
│
├── LetterIssuanceTest.php
│   - test_issue_letter_for_approved_request
│   - test_cannot_issue_for_pending_request
│   - test_issue_consumes_quota
│   - test_cancel_letter_refunds_quota
│   - test_generate_pdf_successfully
│
└── BaleBot/
    ├── RegistrationFlowTest.php
    │   - test_complete_registration_flow
    │   - test_mobile_normalization
    │   - test_state_management
    │   - test_family_members_collection
    │
    └── TrackingTest.php
        - test_track_by_code
        - test_get_letter_pdf
```

### Integration Tests

```php
tests/Integration/
└── CompleteWorkflowTest.php
    - test_full_workflow_web_panel
    - test_full_workflow_bale_bot
    - test_quota_consumption_and_refund
```

### Browser Tests (Optional - Playwright)

```python
tests/browser/
└── test_personnel_request_flow.py
    - test_create_request_with_family_members
    - test_admin_approval_flow
    - test_letter_issuance_flow
```

---

## 📦 Dependencies

### Composer Packages

```bash
# Already installed
composer require spatie/laravel-permission
composer require morilog/jalali

# New packages needed
composer require barryvdh/laravel-dompdf        # PDF generation
composer require simplesoftwareio/simple-qrcode # QR codes for letters
```

### NPM Packages (if needed for frontend)

```bash
npm install --save axios
npm install --save sweetalert2  # For beautiful alerts
```

### Bale Bot SDK

Use official Bale Bot API documentation from `bale.txt`

---

## 🚀 Implementation Plan

### Week 1: Foundation
- [ ] Create migrations (period_id to personnel & letters)
- [ ] Run migrations on dev/staging
- [ ] Update models with new relationships
- [ ] Create QuotaService
- [ ] Create PersonnelService
- [ ] Create LetterService
- [ ] Write unit tests for services

### Week 2: Web Panel (Quota & Requests)
- [ ] QuotaController + views
- [ ] PersonnelRequestController + views
- [ ] JavaScript for family members dynamic form
- [ ] PersonnelApprovalController + views
- [ ] Policies & authorization
- [ ] Feature tests for web panel

### Week 3: Letter Issuance & PDF
- [ ] IntroductionLetterController
- [ ] Letter issuance logic with quota consumption
- [ ] PDF template design
- [ ] PDF generation with dompdf
- [ ] QR code integration
- [ ] Cancel letter + refund logic
- [ ] Feature tests for letters

### Week 4: Bale Bot Integration
- [ ] MobileNumberNormalizer
- [ ] KeyboardBuilder
- [ ] StateManager
- [ ] Bot webhook controller
- [ ] Registration flow implementation
- [ ] Tracking & letter download
- [ ] Bot integration tests
- [ ] End-to-end testing

### Week 5: Polish & Deploy
- [ ] UI/UX improvements
- [ ] Error handling
- [ ] Logging & monitoring
- [ ] Documentation update
- [ ] Security audit
- [ ] Performance testing
- [ ] Deploy to staging
- [ ] User acceptance testing (UAT)
- [ ] Deploy to production

---

## 🔐 Security Considerations

### Authentication & Authorization
- ✅ All routes protected with auth middleware
- ✅ Role-based access control with policies
- ✅ API endpoints require valid Bale user or authenticated user

### Data Validation
- ✅ Server-side validation for all inputs
- ✅ National code format & uniqueness check
- ✅ Mobile number normalization & validation
- ✅ XSS protection (Laravel default escaping)
- ✅ SQL injection prevention (Eloquent ORM)

### Quota Security
- ✅ Atomic quota operations (DB transactions)
- ✅ Race condition prevention with locks
- ✅ Audit log for quota changes

### Personal Data
- ✅ National codes hashed in logs
- ✅ Mobile numbers masked in debug logs
- ✅ GDPR compliance considerations

---

## 📊 Monitoring & Logging

### Metrics to Track
- Total personnel requests (daily/weekly/monthly)
- Approval rate (approved / total)
- Average approval time
- Letters issued per center
- Quota consumption rate
- Bot registration vs web registration ratio
- Failed validations (by type)

### Logs
- Quota allocation/consumption/refund
- Personnel request creation
- Approval/rejection with reasons
- Letter issuance
- Bot interactions (masked data)
- Errors & exceptions

---

## 📝 Documentation Deliverables

- [ ] API documentation (OpenAPI/Swagger)
- [ ] User manual for operators (Persian)
- [ ] Admin guide for quota management (Persian)
- [ ] Bale bot user guide (in-bot /help)
- [ ] Developer documentation
- [ ] Deployment guide
- [ ] Troubleshooting guide

---

## ✨ Future Enhancements (Post Phase 1)

### Phase 2: Lottery System
- Provincial quota distribution
- Priority scoring algorithm
- Automated draw
- Waitlist management

### Phase 3: Advanced Features
- SMS notifications
- Email notifications
- Payment integration
- Mobile app (React Native)
- Advanced reporting & analytics
- Excel import/export

---

## 📞 Support & Maintenance

### Issue Tracking
- GitHub Issues for bug reports
- Feature requests via discussion board

### Rollback Plan
If critical issues in production:
1. Revert last git commit
2. Rollback database migration
3. Clear all caches
4. Restart services

### Backup Strategy
- Daily database backups
- Keep last 30 days
- Test restore monthly

---

**End of Specification**

**Version:** 1.0.0
**Date:** 2026-02-12
**Status:** Ready for Implementation
**Approved By:** [Pending]
**Estimated Completion:** 4-5 weeks from start
