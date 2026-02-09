# Introduction Letter System - سیستم صدور معرفی‌نامه

## Why - چرا

سامانه Welfare 1 فعلی از اتوماسیون اداری برای صدور معرفی‌نامه استفاده می‌کند که باعث پراکندگی اطلاعات و عدم امکان گزارش‌گیری متمرکز شده است. نیاز است که فرآیند صدور معرفی‌نامه به صورت تحت وب و متمرکز در Welfare 2 انجام شود تا مدیریت سهمیه‌ها و گزارش‌گیری بهبود یابد.

The current Welfare 1 system uses office automation for issuing introduction letters, causing scattered data and inability to generate centralized reports. The introduction letter process needs to move to a web-based, centralized system in Welfare 2 to improve quota management and reporting.

### Current Project Status - وضعیت فعلی پروژه

| Item | Value |
|------|-------|
| **Server** | 37.152.174.87:8083 |
| **Progress** | ~40% complete |
| **Admin Panel** | http://37.152.174.87:8083/admin |
| **Default Login** | admin@bankmelli.ir / password |

**Completed Modules / ماژول‌های تکمیل شده:**
- ✅ Centers (مراکز) - 3 records
- ✅ Units (واحدها) - 426 units, 1781 beds
- ✅ Periods (دوره‌ها) - with Jalali dates
- ✅ Sidebar/Layout - modern design

**Critical Issues / مشکلات بحرانی:**
- ❌ **No Authorization** - هر کاربر لاگین شده به همه چیز دسترسی دارد
- ❌ **No Role Middleware** - Route ها بدون محدودیت نقش هستند

## What Changes - چه تغییراتی

### Phase 0: Fix Critical Issues (پیش‌نیاز) ❌ BLOCKER

- **Authorization Implementation**: پیاده‌سازی Policy ها برای همه مدل‌ها
- **Role Middleware**: اضافه کردن middleware به route ها
- **Province Scoping**: محدود کردن دسترسی ادمین استانی به استان خودش

### Phase 1: Introduction Letter System (اصلی)

- **Provincial Letter Issuance / صدور معرفی‌نامه استانی**: هر استان بتواند معرفی‌نامه‌های خود را صادر کند
- **Quota Management / مدیریت سهمیه**: تعریف و کنترل سهمیه هر استان در هر نوبت
- **Quota Tracking / ردیابی سهمیه**: نمایش سهمیه باقیمانده و جلوگیری از تجاوز
- **Auto-Lock / قفل خودکار**: بسته شدن امکان صدور X روز قبل از شروع نوبت
- **Centralized Reporting / گزارش‌گیری متمرکز**: مشاهده تجمیعی و خروجی اکسل
- **Letter Template / قالب معرفی‌نامه**: چاپ معرفی‌نامه با اطلاعات همراهان

## Capabilities - قابلیت‌ها

### New Capabilities - قابلیت‌های جدید

- `authorization`: پیاده‌سازی سیستم مجوزدهی / Implement authorization system
- `quota-allocation`: تخصیص سهمیه به استان‌ها / Allocate quotas to provinces per period
- `letter-issuance`: صدور معرفی‌نامه توسط ادمین استانی / Issue introduction letters by provincial admin
- `quota-tracking`: ردیابی و کنترل سهمیه باقیمانده / Track and control remaining quota
- `auto-lock`: قفل خودکار صدور قبل از نوبت / Auto-lock issuance before period start
- `letter-template`: قالب و چاپ معرفی‌نامه / Letter template and printing
- `reporting`: گزارش‌گیری و خروجی اکسل / Reporting and Excel export

### Modified Capabilities - قابلیت‌های اصلاح شده

- `existing-controllers`: اضافه کردن Authorization به کنترلرهای موجود
- `routes`: اضافه کردن Role Middleware به route ها

## Impact - تأثیرات

### New Files - فایل‌های جدید

```
app/
├── Models/
│   ├── ProvinceQuota.php
│   └── IntroductionLetter.php
├── Services/
│   ├── QuotaService.php
│   ├── LetterService.php
│   └── LetterPdfService.php
├── Policies/
│   ├── IntroductionLetterPolicy.php
│   ├── ProvinceQuotaPolicy.php
│   ├── CenterPolicy.php
│   ├── UnitPolicy.php
│   └── PeriodPolicy.php
├── Http/Controllers/
│   ├── QuotaController.php
│   ├── LetterController.php
│   └── Api/
│       ├── QuotaController.php
│       └── LetterController.php
├── Exports/
│   ├── LettersExport.php
│   └── CompanionsExport.php
└── Console/Commands/
    └── LockExpiredQuotas.php

database/migrations/
├── xxxx_create_province_quotas_table.php
└── xxxx_create_introduction_letters_table.php

resources/views/
├── quotas/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── letters/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
│   └── pdf.blade.php
└── reports/
    └── letters.blade.php
```

### Modified Files - فایل‌های اصلاح شده

```
routes/web.php              # Add role middleware
routes/api.php              # Add role middleware
app/Providers/AuthServiceProvider.php  # Register policies
config/welfare.php          # Add letter settings
```

### APIs - واسط‌های برنامه‌نویسی

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | /api/quotas | لیست سهمیه‌ها | provincial_admin |
| POST | /api/letters | صدور معرفی‌نامه | provincial_admin |
| GET | /api/letters | لیست معرفی‌نامه‌ها | provincial_admin |
| DELETE | /api/letters/{id} | لغو معرفی‌نامه | provincial_admin |
| GET | /api/letters/{number}/verify | استعلام | operator |
| POST | /api/admin/quotas | تخصیص سهمیه | admin |
| GET | /api/reports/letters/export | خروجی اکسل | admin |

### Dependencies - وابستگی‌ها

```bash
# New packages to install
composer require barryvdh/laravel-dompdf    # PDF generation
composer require maatwebsite/excel          # Excel export
composer require simplesoftwareio/simple-qrcode  # QR codes
```

### User Roles - نقش‌های کاربری

| Role | Persian | New Permissions |
|------|---------|-----------------|
| super_admin | مدیر سیستم | * (all) |
| admin | ادمین اداره کل | manage_quotas, view_all_letters, export_reports |
| provincial_admin | مدیر استانی | issue_letters, view_province_letters |
| operator | اپراتور | verify_letters |
| user | همکار | - |

## Timeline - زمان‌بندی

| Phase | Duration | Description |
|-------|----------|-------------|
| Phase 0 | هفته ۱ | Fix critical issues (Authorization) |
| Phase 1.1 | هفته ۲ | Database & Models |
| Phase 1.2 | هفته ۳ | Services (Quota, Letter) |
| Phase 1.3 | هفته ۴ | UI (Admin, Provincial) |
| Phase 1.4 | هفته ۵ | Reporting & PDF |
| Phase 1.5 | هفته ۶ | Testing & Deploy |

**Total: ~6 weeks / مجموع: حدود ۶ هفته**
