# Introduction Letter System Design - طراحی فنی سیستم معرفی‌نامه

## Context - زمینه

### Background - پیش‌زمینه

در سیستم فعلی (Welfare 1)، معرفی‌نامه‌ها از طریق اتوماسیون اداری صادر می‌شوند که باعث:
- پراکندگی اطلاعات در سیستم‌های مختلف
- عدم امکان گزارش‌گیری متمرکز
- نبود کنترل سهمیه به صورت آنی
- دشواری در پیگیری و استعلام

In the current system (Welfare 1), introduction letters are issued through office automation, causing:
- Scattered data across different systems
- Inability to generate centralized reports
- No real-time quota control
- Difficulty in tracking and verification

### Current State - وضعیت فعلی پروژه

**Server Environment / محیط سرور:**
- **Server IP**: 37.152.174.87
- **OS**: Ubuntu 22.04.5 LTS
- **Web Port**: 8083
- **Admin Panel**: http://37.152.174.87:8083/admin

**Tech Stack / استک فنی:**
- **Laravel 11** با معماری Service Layer
- **PostgreSQL** برای ذخیره‌سازی
- **Redis** برای کش و صف
- **Docker** برای deployment
- **Spatie Permission** برای RBAC (نصب شده ولی استفاده نشده!)

**Completed Modules / ماژول‌های تکمیل شده (40%):**
| Module | Status | Records |
|--------|--------|---------|
| Centers (مراکز) | ✅ CRUD کامل | 3 مرکز |
| Units (واحدها) | ✅ CRUD کامل | 426 واحد (1781 تخت) |
| Periods (دوره‌ها) | ✅ CRUD کامل | با تاریخ شمسی |
| Sidebar/Layout | ✅ طراحی مدرن | - |

**Remaining Modules / ماژول‌های باقیمانده:**
- [ ] Lottery (قرعه‌کشی) - Phase 2
- [ ] Reservations (رزروها)
- [ ] Personnel (پرسنل)
- [ ] Provinces (استان‌ها)
- [ ] Reports (گزارش‌ها)
- [ ] **Introduction Letters (معرفی‌نامه) - Phase 1** ← این spec

### Critical Issues - مشکلات بحرانی (باید قبل از Phase 1 رفع شوند)

| Issue | Severity | Description |
|-------|----------|-------------|
| **No Authorization** | ❌ Critical | هر کاربر لاگین شده به همه چیز دسترسی دارد |
| **No Role Middleware** | ❌ Critical | Route ها بدون محدودیت نقش هستند |
| N+1 Queries | ⚠️ Medium | در CenterController |
| No Cache | ⚠️ Medium | داده‌های ثابت هر بار از DB خوانده می‌شوند |
| Fat Controllers | ⚠️ Medium | PeriodController بیش از 200 خط |

### Constraints - محدودیت‌ها

- باید با Welfare 1 همزیستی داشته باشد (فاز انتقالی)
- تعداد کاربران همزمان: ~500 ادمین استانی
- معرفی‌نامه‌ها باید قابل چاپ و استعلام باشند
- سهمیه‌بندی باید دقیق و بدون تداخل باشد
- **باید ابتدا Authorization پیاده‌سازی شود**

### Stakeholders - ذینفعان

| Role | Persian | Access Level |
|------|---------|--------------|
| super_admin | مدیر سیستم | همه چیز |
| admin | ادمین اداره کل | مراکز، قرعه‌کشی، گزارشات |
| provincial_admin | مدیر استانی | تایید/رد، گزارشات استانی |
| operator | اپراتور | مشاهده، ورود داده |
| user | همکار | ثبت‌نام، مشاهده نتایج |

---

## Goals / Non-Goals - اهداف

### Goals - اهداف

1. **رفع مشکلات بحرانی**: Authorization و Role-based Access
2. **مدیریت متمرکز سهمیه**: تخصیص و کنترل سهمیه هر استان
3. **صدور آنلاین**: جایگزینی اتوماسیون با سیستم تحت وب
4. **گزارش‌گیری جامع**: خروجی اکسل و نمای تجمیعی
5. **کنترل دسترسی**: هر استان فقط سهمیه خودش
6. **قفل خودکار**: جلوگیری از صدور دیرهنگام

### Non-Goals - غیراهداف

- قرعه‌کشی خودکار (فاز ۲)
- انتخاب واحد خاص توسط پرسنل
- پرداخت آنلاین
- اپلیکیشن موبایل (فعلاً وب کافی است)

---

## Decisions - تصمیمات

### 0. Fix Critical Issues First - رفع مشکلات بحرانی (پیش‌نیاز)

**Decision**: قبل از شروع Phase 1، Authorization باید پیاده‌سازی شود

```php
// routes/web.php - بعد از اصلاح
Route::middleware(['auth', 'role:super_admin|admin'])->group(function () {
    // Admin routes
    Route::resource('centers', CenterController::class);
    Route::resource('units', UnitController::class);
});

Route::middleware(['auth', 'role:provincial_admin'])->group(function () {
    // Provincial admin routes
    Route::resource('letters', LetterController::class);
});

// app/Policies/IntroductionLetterPolicy.php
public function create(User $user): bool
{
    return $user->hasRole('provincial_admin');
}

public function view(User $user, IntroductionLetter $letter): bool
{
    return $user->province_id === $letter->province_id
        || $user->hasRole(['super_admin', 'admin']);
}
```

### 1. Database Schema

**Decision**: دو جدول اصلی: `province_quotas` و `introduction_letters`

```sql
-- Province Quotas / سهمیه استانی
CREATE TABLE province_quotas (
    id SERIAL PRIMARY KEY,
    province_id INT REFERENCES provinces(id),
    period_id INT REFERENCES periods(id),
    center_id INT REFERENCES centers(id),
    allocated_quota INT NOT NULL DEFAULT 0,
    used_quota INT NOT NULL DEFAULT 0,
    lock_days_before INT NOT NULL DEFAULT 5,
    is_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(province_id, period_id, center_id)
);

-- Introduction Letters / معرفی‌نامه‌ها
CREATE TABLE introduction_letters (
    id SERIAL PRIMARY KEY,
    letter_number VARCHAR(50) UNIQUE NOT NULL,
    province_id INT REFERENCES provinces(id),
    period_id INT REFERENCES periods(id),
    center_id INT REFERENCES centers(id),
    personnel_id INT REFERENCES personnel(id),
    employee_code VARCHAR(20),
    personnel_name VARCHAR(255),
    companions JSONB DEFAULT '[]',
    companion_count INT DEFAULT 0,
    tariff_type VARCHAR(50),
    issued_by INT REFERENCES users(id),
    issued_at TIMESTAMP,
    cancelled_at TIMESTAMP,
    cancelled_by INT REFERENCES users(id),
    cancellation_reason TEXT,
    status VARCHAR(20) DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX idx_letters_province ON introduction_letters(province_id);
CREATE INDEX idx_letters_period ON introduction_letters(period_id);
CREATE INDEX idx_letters_status ON introduction_letters(status);
```

### 2. Letter Number Format

**Decision**: `{YEAR}-{CENTER_CODE}-{PROVINCE_CODE}-{SEQUENCE}`

**Example**: `1403-MSH-THR-00142`

| Part | Description | Example |
|------|-------------|---------|
| YEAR | Jalali year | 1403 |
| CENTER_CODE | 3-letter center code | MSH, BBL, CHD |
| PROVINCE_CODE | 3-letter province code | THR, ISF, KHZ |
| SEQUENCE | 5-digit sequence | 00142 |

### 3. Quota Locking Strategy

**Decision**: Database-level locking with pessimistic locking

```php
DB::transaction(function () {
    $quota = ProvinceQuota::where('id', $quotaId)
        ->lockForUpdate()
        ->first();

    if ($quota->used_quota >= $quota->allocated_quota) {
        throw new QuotaExhaustedException();
    }

    // Create letter
    $letter = IntroductionLetter::create([...]);

    // Increment quota
    $quota->increment('used_quota');
});
```

### 4. Auto-Lock Implementation

**Decision**: Scheduled job + real-time check

```php
// Scheduled job (daily at 00:00)
// app/Console/Commands/LockExpiredQuotas.php
ProvinceQuota::where('is_locked', false)
    ->whereHas('period', function ($q) {
        $q->whereRaw('start_date - lock_days_before <= CURRENT_DATE');
    })
    ->update(['is_locked' => true]);

// Real-time check in service
public function canIssueLetter(ProvinceQuota $quota): bool
{
    if ($quota->is_locked) return false;

    $lockDate = $quota->period->start_date
        ->subDays($quota->lock_days_before);

    return now()->lt($lockDate);
}
```

### 5. PDF Generation

**Decision**: Laravel DomPDF with Blade templates

```php
// app/Services/LetterPdfService.php
public function generate(IntroductionLetter $letter): string
{
    $pdf = Pdf::loadView('letters.pdf', [
        'letter' => $letter,
        'qrCode' => $this->generateQrCode($letter),
    ]);

    return $pdf->output();
}
```

---

## Risks / Trade-offs - ریسک‌ها

### Risk 1: Authorization Not Implemented
**Risk**: سیستم فعلی Authorization ندارد - بحرانی‌ترین مشکل
**Mitigation**: قبل از هر کاری Authorization پیاده‌سازی شود

### Risk 2: Concurrent Quota Access
**Risk**: دو ادمین همزمان سهمیه را مصرف کنند
**Mitigation**: Database locking + unique constraint

### Risk 3: Data Migration from Welfare 1
**Risk**: داده‌های قدیمی در اتوماسیون موجود است
**Mitigation**: Run parallel initially, gradual migration

### Risk 4: PDF Generation Performance
**Risk**: تولید PDF کند باشد
**Mitigation**: Queue heavy PDFs, cache templates

---

## Migration Plan - برنامه مهاجرت

### Phase 0: Fix Critical Issues (هفته ۱) - پیش‌نیاز
1. پیاده‌سازی Authorization Policies
2. اضافه کردن Role Middleware به Routes
3. تست دسترسی‌ها
4. **این فاز باید قبل از همه چیز انجام شود**

### Phase 1.1: Database & Models (هفته ۲)
1. Create migrations
2. Create models (ProvinceQuota, IntroductionLetter)
3. Setup relationships

### Phase 1.2: Core Services (هفته ۳)
1. QuotaService
2. LetterService
3. LetterPdfService
4. Unit tests

### Phase 1.3: Admin Panel (هفته ۴)
1. Quota management UI (admin)
2. Letter issuance UI (provincial_admin)
3. Provincial admin views

### Phase 1.4: Reporting (هفته ۵)
1. Letter listing with filters
2. Excel export
3. PDF generation & print

### Phase 1.5: Testing & Deploy (هفته ۶)
1. UAT with provincial admins
2. Production deployment
3. Documentation

---

## Server Deployment - استقرار در سرور

```bash
# اتصال به سرور
ssh root@37.152.174.87

# رفتن به مسیر پروژه
cd /var/www/welfare

# Pull latest code
git pull origin main

# اجرای migration
docker-compose exec app php artisan migrate

# پاک کردن کش
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# ری‌استارت
docker-compose restart
```

---

## Open Questions - سوالات باز

1. **قالب معرفی‌نامه**: آیا قالب PDF موجود است یا باید طراحی شود؟
2. **تعداد روز قفل**: چند روز قبل از نوبت قفل شود؟ (پیشنهاد: ۵ روز)
3. **سقف همراهان**: حداکثر چند همراه مجاز است؟ (پیشنهاد: ۱۰ نفر)
4. **استعلام عمومی**: آیا اپراتور مرکز دسترسی به استعلام دارد؟
5. **اعلان‌ها**: آیا نیاز به اعلان SMS/Email هست؟
