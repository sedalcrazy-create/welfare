# Quota Tracking - ردیابی سهمیه

## ADDED Requirements - الزامات جدید

### Requirement: Display Remaining Quota - نمایش سهمیه باقیمانده

سیستم باید سهمیه باقیمانده را به ادمین استانی نمایش دهد.
The system SHALL display remaining quota to provincial admin.

#### Scenario: Show Quota Status - نمایش وضعیت سهمیه

- **WHEN** provincial admin views letter issuance page
- **THEN** system SHALL display for each center/period:
  - Total allocated quota / سهمیه کل
  - Used quota / سهمیه مصرفی
  - Remaining quota / سهمیه باقیمانده
  - Percentage used / درصد مصرف
- **AND** visual indicator (progress bar) SHALL show usage

- **وقتی** ادمین استانی صفحه صدور را می‌بیند
- **آنگاه** سیستم باید برای هر مرکز/نوبت نمایش دهد:
  - سهمیه کل، مصرفی، باقیمانده و درصد

#### Scenario: Quota Warning - هشدار سهمیه

- **WHEN** remaining quota <= 20% of allocated
- **THEN** system SHALL display warning message
- **AND** color indicator SHALL change to yellow/red

---

### Requirement: Prevent Over-Quota Issuance - جلوگیری از تجاوز از سهمیه

سیستم باید از صدور معرفی‌نامه بیش از سهمیه جلوگیری کند.
The system SHALL prevent issuing letters beyond allocated quota.

#### Scenario: Block Over-Quota - مسدود کردن بیش از سهمیه

- **WHEN** used_quota >= allocated_quota
- **THEN** system SHALL disable letter issuance
- **AND** display message "سهمیه شما تمام شده است"
- **AND** button SHALL be disabled

#### Scenario: Concurrent Issuance Protection - حفاظت همزمانی

- **WHEN** two admins try to issue simultaneously
- **AND** only 1 quota remaining
- **THEN** system SHALL use database locking
- **AND** only first request SHALL succeed
- **AND** second SHALL receive quota exhausted error

---

### Requirement: Quota Usage History - تاریخچه مصرف سهمیه

سیستم باید تاریخچه مصرف سهمیه را نگهداری کند.
The system SHALL maintain quota usage history.

#### Scenario: Track Quota Changes - ردیابی تغییرات سهمیه

- **WHEN** quota is used (letter issued)
- **OR** quota is freed (letter cancelled)
- **THEN** system SHALL log:
  - Action type (issue/cancel)
  - Letter ID
  - Timestamp
  - Admin who performed action
  - Quota before/after

#### Scenario: View Usage History - مشاهده تاریخچه مصرف

- **WHEN** admin requests quota history
- **THEN** system SHALL display chronological list
- **AND** include all issuances and cancellations
- **AND** show running balance

---

### Requirement: Real-time Quota Update - بروزرسانی آنی سهمیه

سیستم باید سهمیه را به صورت آنی بروز کند.
The system SHALL update quota in real-time.

#### Scenario: Update After Issuance - بروزرسانی پس از صدور

- **WHEN** letter is successfully issued
- **THEN** used_quota SHALL increment by 1 immediately
- **AND** UI SHALL refresh to show new remaining

#### Scenario: Update After Cancellation - بروزرسانی پس از لغو

- **WHEN** letter is cancelled
- **THEN** used_quota SHALL decrement by 1 immediately
- **AND** quota becomes available for new issuance

---

### Requirement: Multi-Center Quota View - نمای سهمیه چند مرکز

ادمین استانی باید سهمیه همه مراکز را یکجا ببیند.
Provincial admin SHALL see quotas for all centers at once.

#### Scenario: Dashboard View - نمای داشبورد

- **WHEN** provincial admin opens dashboard
- **THEN** system SHALL display quota summary:

| Center / مرکز | Period / نوبت | Allocated / تخصیص | Used / مصرف | Remaining / باقی | Status / وضعیت |
|---------------|---------------|-------------------|-------------|------------------|----------------|
| مشهد | ۱۴۰۳/۱۱/۰۱ | ۱۰ | ۶ | ۴ | فعال |
| بابلسر | ۱۴۰۳/۱۱/۰۵ | ۸ | ۸ | ۰ | تمام |
| چادگان | ۱۴۰۳/۱۱/۱۰ | ۵ | ۲ | ۳ | قفل |
