# Quota Allocation - تخصیص سهمیه

## ADDED Requirements - الزامات جدید

### Requirement: Define Period Quotas - تعریف سهمیه نوبت

سیستم باید امکان تخصیص سهمیه به هر استان در هر نوبت را فراهم کند.
The system SHALL allow allocating quotas to each province for each period.

**Data Structure / ساختار داده:**
```
ProvinceQuota {
    province_id: int        // استان
    period_id: int          // نوبت اقامت
    center_id: int          // مرکز رفاهی
    allocated_quota: int    // سهمیه تخصیص‌یافته
    used_quota: int         // سهمیه مصرف‌شده
    lock_days_before: int   // روزهای قفل قبل از نوبت
}
```

#### Scenario: Allocate Quota to Province - تخصیص سهمیه به استان

- **WHEN** admin allocates quota
- **AND** provides: province_id, period_id, center_id, allocated_quota
- **THEN** system SHALL create ProvinceQuota record
- **AND** used_quota SHALL be initialized to 0

- **وقتی** ادمین سهمیه تخصیص می‌دهد
- **و** مشخص می‌کند: استان، نوبت، مرکز، تعداد سهمیه
- **آنگاه** سیستم باید رکورد سهمیه را ایجاد کند
- **و** سهمیه مصرفی باید ۰ باشد

#### Scenario: Update Existing Quota - بروزرسانی سهمیه موجود

- **WHEN** admin updates existing quota
- **AND** new quota >= used_quota
- **THEN** system SHALL update allocated_quota
- **AND** remaining quota SHALL be recalculated

- **وقتی** ادمین سهمیه موجود را بروزرسانی می‌کند
- **و** سهمیه جدید >= سهمیه مصرف‌شده
- **آنگاه** سیستم باید سهمیه را بروز کند

#### Scenario: Prevent Reducing Below Used - جلوگیری از کاهش زیر مصرف

- **WHEN** admin tries to reduce quota
- **AND** new quota < used_quota
- **THEN** system SHALL reject with error
- **AND** return "سهمیه جدید نمی‌تواند کمتر از تعداد مصرف‌شده باشد"

---

### Requirement: Bulk Quota Allocation - تخصیص گروهی سهمیه

سیستم باید امکان تخصیص سهمیه به چند استان به صورت یکجا را فراهم کند.
The system SHALL allow bulk quota allocation to multiple provinces at once.

#### Scenario: Import Quotas from Excel - وارد کردن سهمیه از اکسل

- **WHEN** admin uploads Excel file with quotas
- **AND** file contains: province_code, period_id, quota
- **THEN** system SHALL create/update quotas for all rows
- **AND** return summary of created/updated records

#### Scenario: Copy Quotas from Previous Period - کپی سهمیه از نوبت قبلی

- **WHEN** admin selects "copy from period"
- **AND** chooses source period and target period
- **THEN** system SHALL copy all province quotas
- **AND** reset used_quota to 0 for all

---

### Requirement: Quota Summary View - نمای خلاصه سهمیه

سیستم باید خلاصه سهمیه‌ها را به ادمین نمایش دهد.
The system SHALL display quota summary to admin.

#### Scenario: View All Quotas for Period - مشاهده سهمیه‌های یک نوبت

- **WHEN** admin views period quotas
- **THEN** system SHALL display for each province:
  - Province name / نام استان
  - Allocated quota / سهمیه تخصیصی
  - Used quota / سهمیه مصرفی
  - Remaining quota / سهمیه باقیمانده
  - Lock status / وضعیت قفل
- **AND** totals SHALL be shown at bottom

#### Scenario: Filter by Center - فیلتر بر اساس مرکز

- **WHEN** admin filters by center (Mashhad/Babolsar/Chadegan)
- **THEN** only quotas for that center SHALL be displayed
