# Phase 1 Revised - Proposal

**Date**: 2026-02-09
**Type**: Enhancement
**Status**: Approved
**Priority**: High

---

## 🎯 Problem Statement

نسخه فعلی فاز 1 دارای 3 مشکل اساسی است:

### 1. سهمیه‌بندی نادرست
- **مشکل**: هر کاربر یک سهمیه کلی دارد (مثلاً 100)
- **واقعیت**: کارفرما می‌خواهد سهمیه به تفکیک مرکز باشد
- **مثال**: یوزر A باید 2 سهمیه از مشهد، 3 از بابلسر و 2 از چادگان داشته باشد

### 2. User Creation در Bale Bot
- **مشکل**: وقتی کسی از بات ثبت نام می‌کنه، یک User ساخته میشه
- **نتیجه**: جدول Users پر از کاربرهای غیرضروری می‌شود
- **راه حل**: فقط Personnel ساخته بشه، نه User

### 3. عدم کنترل ثبت نام
- **مشکل**: ثبت نام همیشه فعال است
- **نیاز**: ادمین بتونه برای تاریخ‌های خاص یا مراکز خاص ثبت نام رو ببنده

---

## 💡 Proposed Solution

### 1. Per-Center Quota System

#### جدول جدید: `user_center_quotas`
```
| user_id | center_id | quota_total | quota_used | quota_remaining |
|---------|-----------|-------------|------------|-----------------|
| 1       | 1 (مشهد)  | 2           | 0          | 2               |
| 1       | 2 (بابلسر)| 3           | 1          | 2               |
| 1       | 3 (چادگان)| 2           | 0          | 2               |
```

**Benefits**:
- ✅ دقت بالا در مدیریت سهمیه
- ✅ گزارش‌گیری جداگانه برای هر مرکز
- ✅ کنترل بهتر بر توزیع

---

### 2. Separate Personnel from Users

#### تغییرات در Bale Registration:

**قبل**:
```php
// Creates BOTH User AND Personnel
$user = User::create([...]);
$personnel = Personnel::create([...]);
```

**بعد**:
```php
// Creates ONLY Personnel
$personnel = Personnel::create([
    'registration_source' => 'bale_bot',
    'status' => 'pending',
    // No user creation
]);
```

**Benefits**:
- ✅ جدول Users فقط شامل کارمندان واقعی (admin/operator)
- ✅ Personnel می‌تونه هزاران رکورد داشته باشه بدون کند شدن
- ✅ مدیریت راحت‌تر

---

### 3. Registration Control System

#### جدول جدید: `registration_controls`

**4 نوع قانون**:

1. **Global**: کل سیستم
   ```
   ☑ ثبت نام غیرفعال
   پیام: "سیستم در حال تعمیر است"
   ```

2. **Date Range**: بازه زمانی
   ```
   ☑ 1404/12/01 - 1404/12/15
   پیام: "ثبت نام تا 15 اسفند بسته است"
   ```

3. **Center**: مرکز خاص
   ```
   ☑ مرکز: مشهد
   پیام: "ظرفیت مشهد تکمیل است"
   ```

4. **Period**: دوره خاص
   ```
   ☑ دوره: مشهد - نوروز 1405
   پیام: "ثبت نام برای این دوره بسته شد"
   ```

**Benefits**:
- ✅ کنترل کامل بر ثبت نام
- ✅ مدیریت ساده از پنل ادمین
- ✅ پیام‌های سفارشی به کاربران

---

### 4. Assigned User Tracking

#### تغییر در `introduction_letters`:

```sql
ALTER TABLE introduction_letters
ADD COLUMN assigned_user_id BIGINT;
```

**کاربرد**:
- `issued_by_user_id`: کسی که معرفی‌نامه رو صادر کرده (ادمین)
- `assigned_user_id`: کسی که سهمیه‌اش کم شده (ممکنه فرق کنه)

**سناریو**:
```
اپراتور A معرفی‌نامه‌ای صادر می‌کنه
ولی از سهمیه کارشناس رفاه B کم می‌شه

issued_by_user_id = A
assigned_user_id = B
```

**Benefits**:
- ✅ شفافیت کامل
- ✅ گزارش‌گیری دقیق
- ✅ امکان تفویض سهمیه

---

## 🔄 Migration Strategy

### Phase 1: Database Changes (Day 1)
- ✅ Create `user_center_quotas` table
- ✅ Create `registration_controls` table
- ✅ Add `assigned_user_id` to `introduction_letters`
- ✅ Migrate existing quotas

### Phase 2: Code Changes (Day 2-3)
- ✅ Update models
- ✅ Update controllers
- ✅ Fix Bale registration (remove User creation)

### Phase 3: UI Changes (Day 4-5)
- ✅ User quota management panel (per center)
- ✅ Registration control panel
- ✅ Letter issuance form (user selection)

### Phase 4: Testing (Day 6-7)
- ✅ Unit tests
- ✅ Feature tests
- ✅ End-to-end tests

### Phase 5: Deployment (Day 8)
- ✅ Deploy to staging
- ✅ Test with real data
- ✅ Deploy to production

---

## 📊 Impact Analysis

### Database
- **New Tables**: 2
- **Modified Tables**: 1
- **Data Migration**: Required (simple)

### Code
- **New Models**: 2
- **Modified Models**: 3
- **New Controllers**: 2
- **Modified Controllers**: 2

### UI
- **New Pages**: 2
- **Modified Pages**: 1

### Estimated Time: **1-2 weeks**

---

## ⚠️ Risks & Mitigation

### Risk 1: Data Loss During Migration
- **Mitigation**: Full backup before migration
- **Rollback Plan**: Keep old columns until confirmed working

### Risk 2: Breaking Existing Bale Bot
- **Mitigation**: Keep backward compatibility for 1 week
- **Testing**: Test bot thoroughly on staging

### Risk 3: Performance Impact
- **Mitigation**: Add proper indexes on new tables
- **Monitoring**: Monitor query performance

---

## ✅ Acceptance Criteria

1. ✅ Admin can set quota per center for each user
2. ✅ Bale registration creates ONLY Personnel, not User
3. ✅ Admin can block registration by date/center/period
4. ✅ Letters track which user's quota was used
5. ✅ All existing features continue to work
6. ✅ No data loss
7. ✅ Performance remains acceptable

---

## 🎉 Expected Benefits

### For Admins:
- ✅ دقت بیشتر در مدیریت سهمیه
- ✅ کنترل کامل بر ثبت نام
- ✅ گزارش‌گیری دقیق‌تر

### For System:
- ✅ جدول Users تمیزتر
- ✅ مقیاس‌پذیری بهتر
- ✅ انعطاف‌پذیری بیشتر

### For Users:
- ✅ پیام‌های واضح‌تر
- ✅ تجربه بهتر از بات

---

## 👍 Recommendation

**تأیید می‌شود** برای پیاده‌سازی فوری.

این تغییرات:
1. مشکلات اصلی رو حل می‌کنه
2. خیلی پیچیده نیست
3. backward compatible هست
4. آینده‌نگر است (آماده برای فاز 2)

---

**Proposed By**: Development Team
**Date**: 2026-02-09
**Approved By**: _____________
**Implementation Start**: _____________
