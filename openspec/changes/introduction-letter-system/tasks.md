# Introduction Letter System Tasks - وظایف سیستم معرفی‌نامه

## 0. Fix Critical Issues (پیش‌نیاز - هفته ۱) ❌ BLOCKER

> ⚠️ **این بخش باید قبل از همه چیز انجام شود!**
> سیستم فعلی Authorization ندارد و هر کاربر لاگین شده به همه چیز دسترسی دارد.

### 0.1 Authorization & Policies - مجوزدهی

- [ ] 0.1.1 Create IntroductionLetterPolicy
- [ ] 0.1.2 Create ProvinceQuotaPolicy
- [ ] 0.1.3 Create CenterPolicy (برای ماژول موجود)
- [ ] 0.1.4 Create UnitPolicy (برای ماژول موجود)
- [ ] 0.1.5 Create PeriodPolicy (برای ماژول موجود)
- [ ] 0.1.6 Register policies in AuthServiceProvider
- [ ] 0.1.7 Add `$this->authorize()` to existing controllers

### 0.2 Role Middleware - میدلور نقش

- [ ] 0.2.1 Update routes/web.php - add role middleware to admin routes
- [ ] 0.2.2 Update routes/api.php - add role middleware to API routes
- [ ] 0.2.3 Group routes by role (super_admin, admin, provincial_admin, operator)
- [ ] 0.2.4 Test access restrictions for each role
- [ ] 0.2.5 Add province_id to User model for provincial scoping

### 0.3 Fix Existing Issues - رفع مشکلات موجود

- [ ] 0.3.1 Fix N+1 query in CenterController (use withCount)
- [ ] 0.3.2 Add Cache to frequently accessed data (centers list)
- [ ] 0.3.3 Create Form Request classes for validation
- [ ] 0.3.4 Refactor PeriodController (extract to PeriodService)

---

## 1. Database & Models - دیتابیس و مدل‌ها (هفته ۲)

- [ ] 1.1 Create migration for `province_quotas` table
- [ ] 1.2 Create migration for `introduction_letters` table
- [ ] 1.3 Create ProvinceQuota model with relationships
- [ ] 1.4 Create IntroductionLetter model with relationships
- [ ] 1.5 Add database indexes for performance
- [ ] 1.6 Create model factories for testing
- [ ] 1.7 Create seeders for sample data
- [ ] 1.8 Add `province_id` foreign key to `users` table (if not exists)

---

## 2. Quota Service - سرویس سهمیه (هفته ۳)

- [ ] 2.1 Create QuotaService class
- [ ] 2.2 Implement allocateQuota() method - تخصیص سهمیه
- [ ] 2.3 Implement updateQuota() method - بروزرسانی سهمیه
- [ ] 2.4 Implement getRemainingQuota() method - سهمیه باقیمانده
- [ ] 2.5 Implement incrementUsedQuota() with DB locking - افزایش مصرف با قفل
- [ ] 2.6 Implement decrementUsedQuota() for cancellation - کاهش برای لغو
- [ ] 2.7 Implement getQuotaSummary() for dashboard - خلاصه سهمیه
- [ ] 2.8 Implement bulkAllocate() for Excel import - تخصیص گروهی
- [ ] 2.9 Write unit tests for QuotaService

---

## 3. Letter Service - سرویس معرفی‌نامه (هفته ۳)

- [ ] 3.1 Create LetterService class
- [ ] 3.2 Implement issueLetter() method - صدور معرفی‌نامه
- [ ] 3.3 Implement generateLetterNumber() - تولید شماره یکتا
- [ ] 3.4 Implement cancelLetter() method - لغو معرفی‌نامه
- [ ] 3.5 Implement validateQuota() check - بررسی سهمیه
- [ ] 3.6 Implement validateLockStatus() check - بررسی قفل
- [ ] 3.7 Implement getLetterDetails() - جزئیات معرفی‌نامه
- [ ] 3.8 Implement verifyLetter() for QR scan - استعلام
- [ ] 3.9 Write unit tests for LetterService

---

## 4. Auto-Lock Feature - قفل خودکار (هفته ۳)

- [ ] 4.1 Create LockExpiredQuotas artisan command
- [ ] 4.2 Implement auto-lock logic based on lock_days_before
- [ ] 4.3 Register command in routes/console.php (daily schedule)
- [ ] 4.4 Implement manual lock/unlock methods in QuotaService
- [ ] 4.5 Add real-time lock check in LetterService.issueLetter()
- [ ] 4.6 Create PreLockNotification job (2 days before)
- [ ] 4.7 Write tests for auto-lock scenarios

---

## 5. Quota Management UI - رابط مدیریت سهمیه (هفته ۴)

> Access: `super_admin`, `admin`

- [ ] 5.1 Create QuotaController for admin panel
- [ ] 5.2 Implement index() - list all quotas with filters
- [ ] 5.3 Implement create/store() - allocate quota to province
- [ ] 5.4 Implement edit/update() - modify existing quota
- [ ] 5.5 Implement bulkUpload() - import quotas from Excel
- [ ] 5.6 Implement copyFromPeriod() - copy quotas from previous period
- [ ] 5.7 Create Blade views: quotas/index, create, edit
- [ ] 5.8 Add quota summary widget to admin dashboard
- [ ] 5.9 Add authorization checks (`$this->authorize()`)

---

## 6. Letter Issuance UI - رابط صدور معرفی‌نامه (هفته ۴)

> Access: `provincial_admin` (only own province)

- [ ] 6.1 Create LetterController for provincial admin
- [ ] 6.2 Implement index() - list letters for own province
- [ ] 6.3 Implement create() - show issuance form with remaining quota
- [ ] 6.4 Implement store() - issue new letter
- [ ] 6.5 Implement show() - view letter details
- [ ] 6.6 Implement cancel() - cancel letter (return quota)
- [ ] 6.7 Create personnel search/select component (AJAX)
- [ ] 6.8 Create dynamic companion input form (add/remove rows)
- [ ] 6.9 Show remaining quota prominently on form
- [ ] 6.10 Create Blade views: letters/index, create, show
- [ ] 6.11 Add province scope restriction (middleware or policy)

---

## 7. PDF Generation - تولید PDF (هفته ۵)

- [ ] 7.1 Install and configure Laravel DomPDF (`composer require barryvdh/laravel-dompdf`)
- [ ] 7.2 Create letter PDF Blade template (RTL/Persian support)
- [ ] 7.3 Add Bank Melli logo to resources/images
- [ ] 7.4 Design official header with letter number and date
- [ ] 7.5 Implement companions table in PDF template
- [ ] 7.6 Add QR code generation (use `simplesoftwareio/simple-qrcode`)
- [ ] 7.7 Add security watermark ("بانک ملی ایران")
- [ ] 7.8 Add "CANCELLED" watermark for cancelled letters
- [ ] 7.9 Implement print preview route (returns HTML)
- [ ] 7.10 Implement PDF download route

---

## 8. Excel Export - خروجی اکسل (هفته ۵)

- [ ] 8.1 Install Laravel Excel (`composer require maatwebsite/excel`)
- [ ] 8.2 Create LettersExport class with columns:
  - ردیف، شماره معرفی‌نامه، استان، کد استخدامی، نام پرسنل
  - مرکز، تاریخ نوبت، تعداد همراهان، تاریخ صدور، وضعیت
- [ ] 8.3 Implement filtering (by province, center, period, date range)
- [ ] 8.4 Create CompanionsExport class (one row per companion)
- [ ] 8.5 Add export buttons to UI (filtered + all)
- [ ] 8.6 Implement large export handling (queue if > 1000 rows)

---

## 9. Reporting Dashboard - داشبورد گزارش‌گیری (هفته ۵)

- [ ] 9.1 Create ReportController
- [ ] 9.2 Implement letters listing with advanced filters
- [ ] 9.3 Implement period summary view (per province stats)
- [ ] 9.4 Implement provincial summary view (per center stats)
- [ ] 9.5 Add statistics widgets:
  - معرفی‌نامه‌های امروز
  - کل معرفی‌نامه‌ها
  - درصد استفاده از سهمیه
- [ ] 9.6 Create audit log view (who issued what, when)
- [ ] 9.7 Add chart visualizations (Chart.js)

---

## 10. Access Control - کنترل دسترسی (هفته ۴)

- [ ] 10.1 Define permissions in database seeder:
  - `manage_quotas` (admin)
  - `issue_letters` (provincial_admin)
  - `view_all_letters` (admin)
  - `view_province_letters` (provincial_admin)
  - `export_reports` (admin)
- [ ] 10.2 Assign permissions to roles
- [ ] 10.3 Create ProvinceScopeMiddleware (restricts to own province)
- [ ] 10.4 Register IntroductionLetterPolicy
- [ ] 10.5 Register ProvinceQuotaPolicy
- [ ] 10.6 Write feature tests for access control

---

## 11. API Endpoints - واسط‌های API (هفته ۵)

> For future mobile/Bale integration

- [ ] 11.1 Create Api/QuotaController
- [ ] 11.2 `GET /api/quotas` - list quotas for authenticated province
- [ ] 11.3 Create Api/LetterController
- [ ] 11.4 `GET /api/letters` - list letters with filters
- [ ] 11.5 `POST /api/letters` - issue new letter
- [ ] 11.6 `DELETE /api/letters/{id}` - cancel letter
- [ ] 11.7 `GET /api/letters/{id}` - get letter details
- [ ] 11.8 `GET /api/letters/{number}/verify` - verify letter by number
- [ ] 11.9 `GET /api/reports/letters/export` - trigger Excel export
- [ ] 11.10 Write API tests with Sanctum authentication

---

## 12. Notifications - اعلان‌ها (هفته ۶)

- [ ] 12.1 Create QuotaLowNotification (when < 20% remaining)
- [ ] 12.2 Create PreLockNotification (2 days before lock)
- [ ] 12.3 Create QuotaLockedNotification (when period locks)
- [ ] 12.4 Configure notification channels (database, mail)
- [ ] 12.5 Create notification preferences UI

---

## 13. Testing - تست (هفته ۶)

- [ ] 13.1 Write unit tests for ProvinceQuota model
- [ ] 13.2 Write unit tests for IntroductionLetter model
- [ ] 13.3 Write unit tests for QuotaService
- [ ] 13.4 Write unit tests for LetterService
- [ ] 13.5 Write feature tests for quota management flow
- [ ] 13.6 Write feature tests for letter issuance flow
- [ ] 13.7 Write feature tests for reporting
- [ ] 13.8 Test concurrent quota access (race condition simulation)
- [ ] 13.9 Test auto-lock scheduled command
- [ ] 13.10 UAT with 5 provincial admins on staging

---

## 14. Deployment - استقرار (هفته ۶)

- [ ] 14.1 Run migrations on production server
```bash
ssh root@37.152.174.87
cd /var/www/welfare
docker-compose exec app php artisan migrate
```
- [ ] 14.2 Seed permissions and roles
- [ ] 14.3 Create provincial admin accounts (37 users)
- [ ] 14.4 Clear caches
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```
- [ ] 14.5 Test all flows in production
- [ ] 14.6 Monitor logs for errors

---

## 15. Documentation - مستندات (هفته ۶)

- [ ] 15.1 API documentation (OpenAPI/Swagger format)
- [ ] 15.2 Admin user guide: quota management
- [ ] 15.3 Provincial admin user guide: letter issuance
- [ ] 15.4 Operator guide: letter verification
- [ ] 15.5 Update CLAUDE.md with new modules

---

## Summary - خلاصه

| Phase | Tasks | Status |
|-------|-------|--------|
| 0. Fix Critical Issues | 16 tasks | ❌ BLOCKER |
| 1. Database & Models | 8 tasks | ⬜ Pending |
| 2-4. Services | 25 tasks | ⬜ Pending |
| 5-6. UI | 20 tasks | ⬜ Pending |
| 7-9. PDF/Excel/Reports | 23 tasks | ⬜ Pending |
| 10-11. Access & API | 16 tasks | ⬜ Pending |
| 12-15. Final | 21 tasks | ⬜ Pending |
| **Total** | **~130 tasks** | |
