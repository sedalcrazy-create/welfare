# ✅ Phase 1 Implementation Checklist

## سوالات شما:

### 1️⃣ همه منوهای مورد نیاز در داشبورد هست؟

✅ **بله، الان کامل شد!**

منوهای Phase 1 در sidebar:
- ✅ **درخواست‌ها** (`personnel-requests.index`) - با بج تعداد pending
- ✅ **تأیید درخواست‌ها** (`admin.personnel-approvals.pending`) - جدید اضافه شد! با بج قرمز
- ✅ **معرفی‌نامه‌ها** (`introduction-letters.index`)
- ✅ **سهمیه (به تفکیک مرکز)** (`admin.user-center-quota.index`)
- ✅ **کنترل ثبت نام** (`admin.registration-control.index`)

**نکته مهم**: منوی "تأیید درخواست‌ها" را الان اضافه کردم که قبلاً نبود.

---

### 2️⃣ اسپک فاز 1 کامل پیاده شد؟

✅ **بله، 100% کامل پیاده شد!**

#### Backend (کامل ✅)

**Database & Models:**
- ✅ Migration: `preferred_period_id` به جدول personnel اضافه شد
- ✅ Migration: `period_id` به جدول introduction_letters اضافه شد
- ✅ Personnel model: رابطه preferredPeriod
- ✅ IntroductionLetter model: رابطه period + generateLetterCode با پشتیبانی از period
- ✅ Period model: متدهای کمکی و روابط

**Services (3 سرویس):**
- ✅ UserQuotaService: مدیریت کامل سهمیه (allocate, consume, refund, check, increase, decrease, reset)
- ✅ PersonnelRequestService: چرخه حیات درخواست (create, approve, reject, update, delete)
- ✅ IntroductionLetterService: صدور معرفی‌نامه (issue, cancel, PDF)
- ✅ MobileNumberNormalizer: عادی‌سازی شماره موبایل (فارسی/انگلیسی/عربی)

**Controllers (6 کنترلر):**
- ✅ Admin\QuotaController: 6 متد (index, allocate, update, reset, increase, decrease)
- ✅ Admin\PersonnelApprovalController: 7 متد (pending, show, approve, reject, bulkApprove, bulkReject)
- ✅ Api\CenterController: لیست مراکز
- ✅ Api\PeriodController: لیست دوره‌ها با فیلتر
- ✅ Api\PersonnelRequestController: ثبت‌نام با عادی‌سازی موبایل
- ✅ PersonnelRequestController: ثبت‌نام وب با انتخاب دوره

**Form Requests (8 درخواست):**
- ✅ AllocateQuotaRequest
- ✅ UpdateQuotaRequest
- ✅ IssueLetterRequest
- ✅ CancelLetterRequest
- ✅ RejectPersonnelRequest
- ✅ Api\RegisterPersonnelRequest
- ✅ StorePersonnelRequest (به‌روز شد با period_id)
- ✅ UpdatePersonnelRequest (موجود بود)

**Authorization:**
- ✅ UserCenterQuotaPolicy: مجوزهای مدیریت سهمیه
- ✅ PersonnelPolicy: متدهای approve/reject اضافه شد

**Routes:**
- ✅ Web: `/admin/quotas/*`, `/admin/personnel-approvals/*`
- ✅ API: `/api/v1/centers`, `/api/v1/periods`, `/api/v1/personnel-requests/*`

#### Frontend (کامل ✅)

**Views:**
- ✅ `admin/quotas/index.blade.php` - مدیریت سهمیه با کارت‌های آماری و مودال‌ها
- ✅ `admin/personnel-approvals/pending.blade.php` - لیست با فیلتر و عملیات گروهی
- ✅ `admin/personnel-approvals/show.blade.php` - نمایش جزئیات با دکمه‌های تأیید/رد
- ✅ `personnel-requests/create.blade.php` - به‌روز شد با dropdown انتخاب دوره
- ✅ `personnel-requests/edit.blade.php` - به‌روز شد با dropdown انتخاب دوره

**UI Features:**
- ✅ Bootstrap 5 modals
- ✅ Inline forms
- ✅ Persian date display (jdate)
- ✅ Badge نمایش وضعیت
- ✅ Alert messages
- ✅ Responsive design
- ✅ منوی sidebar با بج تعداد pending

#### Documentation (کامل ✅)
- ✅ OpenSpec کامل: `openspec/changes/phase1-introduction-letter-system/spec.md` (967 خط)
- ✅ Deployment Guide: `PHASE1_DEPLOYMENT.md` (228 خط)
- ✅ این Checklist

---

### 3️⃣ همه‌چیز تست کردی؟

❌ **نه، هنوز تست نشده!**

**دلیل**: کد روی سرور deploy نشده. تمام کدها فقط local نوشته شده و commit شده.

#### چیزهایی که باید روی سرور تست شوند:

**🔴 High Priority Tests (باید حتماً تست شوند)**

1. **Migration:**
   ```bash
   docker-compose exec app php artisan migrate
   # باید 2 migration جدید اجرا شود
   ```

2. **Personnel Registration با Period Selection:**
   - ✅ فرم `/personnel-requests/create` باز می‌شود؟
   - ✅ Dropdown دوره‌ها نمایش داده می‌شود؟
   - ✅ می‌شه یه period انتخاب کرد؟
   - ✅ Submit می‌شود بدون خطا؟
   - ✅ Validation اگر period انتخاب نشه کار می‌کند؟

3. **Admin Approval Workflow:**
   - ✅ صفحه `/admin/personnel-approvals/pending` باز می‌شود؟
   - ✅ لیست درخواست‌های pending نمایش داده می‌شود؟
   - ✅ فیلترها کار می‌کنند؟
   - ✅ دکمه تأیید کار می‌کند؟
   - ✅ دکمه رد با modal دلیل کار می‌کند؟
   - ✅ بعد از تأیید redirect به صفحه صدور معرفی‌نامه می‌شود؟

4. **Quota Management:**
   - ✅ صفحه `/admin/quotas/users/{user_id}` باز می‌شود؟
   - ✅ کارت‌های سهمیه نمایش داده می‌شوند؟
   - ✅ Modal های افزایش/کاهش/ویرایش کار می‌کنند؟
   - ✅ سهمیه در دیتابیس ذخیره می‌شود؟

5. **Mobile Number Normalizer (برای Bale Bot - خیلی مهم!):**
   ```bash
   # Test 1: Persian digits
   curl -X POST http://37.152.174.87:8083/api/v1/personnel-requests/register \
     -H "Content-Type: application/json" \
     -d '{"employee_code":"T001","full_name":"تست","national_code":"1234567890","phone":"۰۹۱۲۳۴۵۶۷۸۹","preferred_center_id":1,"preferred_period_id":1}'

   # Test 2: +98 format
   curl -X POST http://37.152.174.87:8083/api/v1/personnel-requests/register \
     -H "Content-Type: application/json" \
     -d '{"employee_code":"T002","full_name":"تست","national_code":"1234567891","phone":"+989123456789","preferred_center_id":1,"preferred_period_id":1}'

   # Test 3: Without leading zero
   curl -X POST http://37.152.174.87:8083/api/v1/personnel-requests/register \
     -H "Content-Type: application/json" \
     -d '{"employee_code":"T003","full_name":"تست","national_code":"1234567892","phone":"9123456789","preferred_center_id":1,"preferred_period_id":1}'
   ```

**🟡 Medium Priority Tests**

6. **API Endpoints:**
   - ✅ `/api/v1/centers` - لیست مراکز
   - ✅ `/api/v1/periods` - لیست دوره‌ها
   - ✅ `/api/v1/periods?center_id=1` - فیلتر بر اساس مرکز
   - ✅ `/api/v1/periods?status=open` - فیلتر بر اساس وضعیت

7. **Family Members:**
   - ✅ اضافه کردن همراه در فرم
   - ✅ حذف همراه
   - ✅ Validation فیلدهای همراه
   - ✅ ذخیره همراهان در JSON

8. **Authorization:**
   - ✅ کاربر عادی نمی‌تونه به `/admin/quotas` دسترسی داشته باشد
   - ✅ کاربر عادی نمی‌تونه به `/admin/personnel-approvals` دسترسی داشته باشد
   - ✅ Provincial admin فقط استان خودش رو ببینه

**🟢 Low Priority Tests**

9. **Bulk Operations:**
   - چک‌باکس select all
   - Bulk approve
   - Bulk reject

10. **Edge Cases:**
    - سهمیه نداشتن در زمان تأیید
    - دوره‌ای که گذشته باشد
    - کد ملی تکراری

---

## آماده برای Deploy؟

### ✅ آماده (Complete)
- [x] تمام کدها نوشته شده
- [x] همه views ساخته شده
- [x] Routes تعریف شده
- [x] Migrations آماده
- [x] منوهای sidebar اضافه شده
- [x] Commit شده در Git (3 commits)
- [x] Documentation کامل

### ⏳ منتظر Deploy (Pending)
- [ ] Push به server repository
- [ ] Run migrations on server
- [ ] Clear caches
- [ ] Test workflows
- [ ] Fix any bugs found during testing
- [ ] Integrate with Bale Bot

---

## خلاصه پاسخ

| سوال | پاسخ | وضعیت |
|------|------|-------|
| همه منوها در داشبورد هست؟ | بله، الان کامل است | ✅ |
| اسپک فاز 1 کامل پیاده شد؟ | بله، 100% | ✅ |
| همه‌چیز تست شد؟ | نه، منتظر deploy روی سرور | ❌ |

---

## مرحله بعد: Deploy و Test

**دستور اول روی سرور:**
```bash
cd /path/to/welfare-V2
git pull origin main
docker-compose exec app php artisan migrate
docker-compose exec app php artisan optimize
docker-compose restart app queue
```

**دستور اول برای تست:**
```bash
# بعد از deploy، این رو تست کن:
curl http://37.152.174.87:8083/api/v1/centers
```

اگر JSON مراکز برگشت، یعنی Phase 1 کار می‌کند! 🎉
