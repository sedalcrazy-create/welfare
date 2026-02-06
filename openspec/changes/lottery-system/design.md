# Lottery System Design - طراحی فنی سیستم قرعه‌کشی

## Context

### Background

سیستم قرعه‌کشی مراکز رفاهی بانک ملی ایران برای توزیع عادلانه ظرفیت محدود ۴۲۶ واحد اقامتی بین حدود ۷۰,۰۰۰ پرسنل طراحی شده است. این سیستم باید:
- عدالت توزیعی بین ۳۷ استان/اداره امور را تضمین کند
- پرسنل با اولویت بالاتر (ایثارگران، سابقه بیشتر) را در نظر بگیرد
- از انحصار استفاده توسط گروه خاصی جلوگیری کند

### Current State

- **Laravel 11** با معماری Service Layer
- **PostgreSQL** برای ذخیره‌سازی
- **Redis** برای کش و صف
- **Spatie Permission** برای RBAC

### Constraints

- قرعه‌کشی باید atomic باشد (همه یا هیچ)
- محاسبات باید دقیق و قابل حسابرسی باشند
- سیستم باید load ۷۰,۰۰۰ کاربر همزمان را تحمل کند

### Stakeholders

- **پرسنل**: ۷۰,۰۰۰ کارمند بانک
- **مدیران استانی**: ۳۷ مدیر برای تأیید برندگان
- **اداره کل رفاه**: مدیریت و گزارش‌گیری
- **تیم فنی**: توسعه و نگهداری

---

## Goals / Non-Goals

### Goals

1. **عدالت توزیعی**: توزیع متناسب با جمعیت هر استان
2. **شفافیت**: نمایش جزئیات امتیاز به کاربران
3. **کارایی**: اجرای قرعه‌کشی ۷۰,۰۰۰ نفری در کمتر از ۱ دقیقه
4. **قابلیت حسابرسی**: ثبت کامل تاریخچه برای بررسی
5. **انعطاف‌پذیری**: قابل تنظیم بودن پارامترهای امتیازدهی

### Non-Goals

- سیستم پرداخت آنلاین (فعلاً حضوری)
- انتخاب واحد خاص توسط کاربر (تخصیص خودکار)
- قرعه‌کشی real-time (batch processing کافی است)
- اپلیکیشن موبایل native (فعلاً Bale mini-app)

---

## Decisions

### 1. Service Layer Architecture

**Decision**: استفاده از Service Classes جداگانه برای هر concern

**Rationale**:
- `LotteryService`: مدیریت چرخه عمر و orchestration
- `PriorityScoreService`: محاسبه امتیاز
- `QuotaService`: محاسبه سهمیه

**Alternatives Considered**:
- Single monolithic service: رد شد به دلیل complexity
- Event-driven: پیچیدگی غیرضروری برای این scale

### 2. Priority Scoring Algorithm

**Decision**: الگوریتم ترکیبی با وزن‌های قابل تنظیم

```php
score = base + days_bonus + service_bonus - usage_penalty + bonuses + random
```

**Rationale**:
- تعادل بین merit (سابقه) و fairness (شانس)
- قابل تنظیم از config بدون تغییر کد
- random factor برای جلوگیری از قطعیت

**Alternatives Considered**:
- Pure random: عدالت را نقض می‌کند
- Pure merit-based: شانس را حذف می‌کند
- ML-based: over-engineering برای این use case

### 3. Provincial Quota Distribution

**Decision**: توزیع نسبی بر اساس جمعیت با تنظیم rounding

**Rationale**:
- ساده و قابل فهم
- منصفانه برای همه استان‌ها
- iterative adjustment برای دقت ۱۰۰٪

**Algorithm**:
```php
foreach (provinces) {
    quota[province] = floor(capacity * ratio[province])
}
while (sum(quota) < capacity) {
    // Add to largest provinces
}
```

### 4. Database Transaction Strategy

**Decision**: Single transaction برای کل عملیات draw

**Rationale**:
- Atomicity تضمین می‌شود
- Recovery ساده در صورت خطا
- Performance قابل قبول برای batch size

**Implementation**:
```php
DB::transaction(function () use ($lottery) {
    // 1. Calculate quotas
    // 2. Score entries
    // 3. Assign winners
    // 4. Update statistics
});
```

### 5. Entry Status State Machine

**Decision**: Explicit status enum با transitions تعریف شده

```
PENDING → WON → APPROVED → (Reservation)
       ↘ WAITLIST ↗ (via promotion)
         ↘ REJECTED
         ↘ EXPIRED
PENDING → CANCELLED (by user)
```

**Rationale**:
- State machine واضح
- Audit trail کامل
- Validation در هر transition

### 6. Waitlist Promotion Strategy

**Decision**: Per-province FIFO با automatic trigger

**Rationale**:
- سهمیه استانی حفظ می‌شود
- بدون تداخل با سایر استان‌ها
- Trigger خودکار روی rejection

---

## Risks / Trade-offs

### Risk 1: Race Condition در Draw
**Risk**: دو admin همزمان draw را trigger کنند
**Mitigation**: Database lock روی lottery record + status check

### Risk 2: Performance با ۷۰,۰۰۰ Entry
**Risk**: کندی محاسبات در scale بالا
**Mitigation**:
- Batch processing
- Index روی province_id, status
- Eager loading relationships

### Risk 3: Random Factor Manipulation
**Risk**: ادعای تقلب در random factor
**Mitigation**:
- Log کردن random seed
- Reproducible با seed مشخص
- Audit trail کامل

### Trade-off 1: Simplicity vs Flexibility
**Choice**: Config-based parameters بجای admin UI
**Impact**: تغییرات نیاز به deployment دارند
**Reason**: کاهش complexity UI

### Trade-off 2: Sync vs Async Draw
**Choice**: Synchronous draw execution
**Impact**: Admin باید منتظر بماند (~30 sec)
**Reason**: ساده‌تر و error handling بهتر

---

## Migration Plan

### Phase 1: Core Services (هفته ۱)
1. پیاده‌سازی PriorityScoreService
2. پیاده‌سازی QuotaService
3. Unit tests

### Phase 2: Lottery Service (هفته ۲)
1. پیاده‌سازی LotteryService.draw()
2. پیاده‌سازی waitlist promotion
3. Integration tests

### Phase 3: API & Controllers (هفته ۳)
1. User API endpoints
2. Admin endpoints
3. Provincial admin endpoints

### Phase 4: Testing & Deployment (هفته ۴)
1. Load testing با ۷۰,۰۰۰ entries
2. UAT با مدیران استانی
3. Production deployment

### Rollback Strategy
- Feature flag برای غیرفعال کردن قرعه‌کشی
- Database backup قبل از هر draw
- Manual override برای emergency

---

## Open Questions

1. **Notification Channel**: Bale، SMS، یا هر دو؟
2. **Approval Deadline**: چند روز برای تأیید استانی؟
3. **Waitlist Size**: همه یا فقط N نفر اول؟
4. **Audit Retention**: چند سال نگهداری logs؟
