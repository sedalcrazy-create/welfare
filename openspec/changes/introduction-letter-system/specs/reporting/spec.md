# Reporting - گزارش‌گیری

## ADDED Requirements - الزامات جدید

### Requirement: Centralized Letter View - نمای متمرکز معرفی‌نامه‌ها

ادمین مرکزی باید بتواند همه معرفی‌نامه‌ها را به صورت تجمیعی ببیند.
Central admin SHALL be able to view all letters aggregated.

#### Scenario: View All Letters - مشاهده همه معرفی‌نامه‌ها

- **WHEN** central admin opens letters report
- **THEN** system SHALL display:
  - Letter number / شماره معرفی‌نامه
  - Province / استان
  - Personnel name / نام پرسنل
  - Employee code / کد استخدامی
  - Center / مرکز
  - Period dates / تاریخ نوبت
  - Companion count / تعداد همراهان
  - Issue date / تاریخ صدور
  - Status / وضعیت
- **AND** pagination SHALL be applied

#### Scenario: Filter Letters - فیلتر معرفی‌نامه‌ها

- **WHEN** admin applies filters
- **THEN** system SHALL filter by:
  - Province / استان
  - Center / مرکز
  - Period / نوبت
  - Date range / بازه تاریخ
  - Status (active/cancelled) / وضعیت
- **AND** filters SHALL be combinable

---

### Requirement: Excel Export - خروجی اکسل

سیستم باید خروجی اکسل از معرفی‌نامه‌ها ارائه دهد.
The system SHALL provide Excel export of letters.

**Excel Columns / ستون‌های اکسل:**

| Column / ستون | Description / توضیح |
|---------------|---------------------|
| ردیف | Row number |
| شماره معرفی‌نامه | Letter number |
| استان | Province name |
| کد استخدامی | Employee code |
| نام پرسنل | Personnel name |
| کد ملی پرسنل | Personnel national code |
| مرکز رفاهی | Center name |
| تاریخ شروع نوبت | Period start date |
| تاریخ پایان نوبت | Period end date |
| تعداد همراهان | Companion count |
| نام همراهان | Companion names (comma-separated) |
| نوع تعرفه | Tariff type |
| تاریخ صدور | Issue date |
| صادرکننده | Issued by |
| وضعیت | Status |

#### Scenario: Export Filtered Data - خروجی داده‌های فیلتر شده

- **WHEN** admin clicks "Export to Excel"
- **AND** filters are applied
- **THEN** system SHALL export only filtered records
- **AND** filename SHALL include date and filters

#### Scenario: Export All Data - خروجی همه داده‌ها

- **WHEN** admin clicks "Export All"
- **THEN** system SHALL export all letters
- **AND** warn if count > 10,000 records

---

### Requirement: Provincial Report - گزارش استانی

هر استان باید گزارش معرفی‌نامه‌های خود را ببیند.
Each province SHALL view their own letters report.

#### Scenario: Province Scoped View - نمای محدود به استان

- **WHEN** provincial admin views report
- **THEN** system SHALL only show their province letters
- **AND** filter by province SHALL be disabled/hidden

#### Scenario: Provincial Summary - خلاصه استانی

- **WHEN** provincial admin views summary
- **THEN** system SHALL display:
  - Total letters issued / کل معرفی‌نامه‌های صادره
  - By center breakdown / تفکیک بر اساس مرکز
  - By period breakdown / تفکیک بر اساس نوبت
  - Cancelled count / تعداد لغو شده

---

### Requirement: Summary Statistics - آمار خلاصه

سیستم باید آمار خلاصه را نمایش دهد.
The system SHALL display summary statistics.

#### Scenario: Dashboard Statistics - آمار داشبورد

- **WHEN** admin views dashboard
- **THEN** system SHALL display:
  - Total letters today / معرفی‌نامه‌های امروز
  - Total letters this period / معرفی‌نامه‌های این نوبت
  - Quota utilization % / درصد استفاده از سهمیه
  - Top provinces by usage / استان‌های پرمصرف

#### Scenario: Period Summary - خلاصه نوبت

- **WHEN** admin views period summary
- **THEN** system SHALL display for each province:
  - Quota allocated / سهمیه تخصیصی
  - Quota used / سهمیه مصرفی
  - Letters issued / معرفی‌نامه صادر شده
  - Letters cancelled / معرفی‌نامه لغو شده

---

### Requirement: Audit Report - گزارش حسابرسی

سیستم باید گزارش حسابرسی برای پیگیری ارائه دهد.
The system SHALL provide audit report for tracking.

#### Scenario: View Audit Trail - مشاهده تاریخچه

- **WHEN** super_admin requests audit report
- **THEN** system SHALL display:
  - All letter issuances
  - All cancellations with reasons
  - Quota changes
  - Admin actions with timestamps

#### Scenario: Export Audit Log - خروجی لاگ حسابرسی

- **WHEN** super_admin exports audit log
- **THEN** system SHALL include:
  - Action type
  - Timestamp
  - Actor (admin user)
  - Affected record
  - Before/after values

---

### Requirement: Companion Report - گزارش همراهان

سیستم باید گزارش تفصیلی همراهان را ارائه دهد.
The system SHALL provide detailed companion report.

#### Scenario: Export Companions - خروجی همراهان

- **WHEN** admin exports companion report
- **THEN** each companion SHALL be a separate row:

| ردیف | شماره معرفی‌نامه | کد استخدامی | نام پرسنل | نام همراه | کد ملی همراه | نسبت |
|------|-----------------|-------------|-----------|-----------|--------------|------|
| ۱ | 1403-MSH-001 | 12345 | احمد محمدی | فاطمه محمدی | 00123456 | همسر |
| ۲ | 1403-MSH-001 | 12345 | احمد محمدی | علی محمدی | 00234567 | فرزند |
