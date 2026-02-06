# Auto-Lock - قفل خودکار

## ADDED Requirements - الزامات جدید

### Requirement: Lock Period Before Start - قفل نوبت قبل از شروع

سیستم باید امکان صدور معرفی‌نامه را X روز قبل از شروع نوبت قفل کند.
The system SHALL lock letter issuance X days before period start.

#### Scenario: Auto-Lock Activation - فعال شدن قفل خودکار

- **WHEN** current date >= (period_start_date - lock_days_before)
- **THEN** system SHALL prevent new letter issuance for that period
- **AND** status SHALL change to "locked"
- **AND** message SHALL display "صدور معرفی‌نامه برای این نوبت قفل شده است"

- **وقتی** تاریخ فعلی >= (تاریخ شروع نوبت - روزهای قفل)
- **آنگاه** سیستم باید صدور را مسدود کند
- **و** وضعیت به "قفل شده" تغییر کند

**Example / مثال:**
- Period starts: ۱۴۰۳/۱۱/۰۶ (Day 6)
- Lock days: 5
- Lock date: ۱۴۰۳/۱۱/۰۱ (Day 1)
- From Day 1, no more letters for Day 6 period

#### Scenario: Different Lock Days Per Center - روزهای قفل متفاوت

- **WHEN** admin configures lock_days_before
- **THEN** each center MAY have different lock days
- **AND** default SHALL be configurable in system settings

---

### Requirement: Lock Status Display - نمایش وضعیت قفل

سیستم باید وضعیت قفل را به کاربر نمایش دهد.
The system SHALL display lock status to user.

#### Scenario: Show Countdown - نمایش شمارش معکوس

- **WHEN** period is not yet locked
- **THEN** system SHALL display:
  - Days remaining until lock / روز مانده تا قفل
  - Lock date / تاریخ قفل
  - Warning if < 3 days remaining

#### Scenario: Show Locked Status - نمایش وضعیت قفل شده

- **WHEN** period is locked
- **THEN** system SHALL display:
  - Lock icon / آیکون قفل
  - "صدور برای این نوبت بسته شده است"
  - Period start date for reference

---

### Requirement: Manual Lock Override - قفل دستی

ادمین باید بتواند به صورت دستی یک نوبت را قفل یا باز کند.
Admin SHALL be able to manually lock/unlock a period.

#### Scenario: Manual Early Lock - قفل زودهنگام دستی

- **WHEN** super_admin manually locks period
- **THEN** system SHALL prevent new issuances immediately
- **AND** record admin who locked and timestamp
- **AND** log reason for early lock

#### Scenario: Manual Unlock - باز کردن دستی قفل

- **WHEN** super_admin manually unlocks period
- **AND** period has not started yet
- **THEN** system SHALL allow new issuances
- **AND** auto-lock SHALL still apply when date reaches

#### Scenario: Prevent Unlock After Start - جلوگیری از باز کردن پس از شروع

- **WHEN** admin tries to unlock
- **AND** period has already started
- **THEN** system SHALL reject with error
- **AND** return "امکان باز کردن قفل پس از شروع نوبت وجود ندارد"

---

### Requirement: Lock Notification - اعلان قفل

سیستم باید به ادمین‌های استانی اطلاع دهد که نوبت در شرف قفل شدن است.
The system SHALL notify provincial admins when period is about to lock.

#### Scenario: Pre-Lock Warning - هشدار قبل از قفل

- **WHEN** lock date is 2 days away
- **THEN** system SHALL send notification to provincial admins
- **AND** notification SHALL include:
  - Period details
  - Remaining quota
  - Lock date and time
  - "برای صدور معرفی‌نامه‌های باقیمانده اقدام کنید"

#### Scenario: Lock Confirmation - تأیید قفل

- **WHEN** period becomes locked
- **THEN** system SHALL send notification to:
  - Provincial admins (their period is locked)
  - Central admin (summary of all locked)
