# Lottery Registration - ثبت‌نام در قرعه‌کشی

## ADDED Requirements

### Requirement: User Registration

کاربران باید بتوانند در قرعه‌کشی‌های فعال ثبت‌نام کنند.

#### Scenario: Successful Registration

- **WHEN** user submits registration
- **AND** lottery status is OPEN
- **AND** current date is within registration window
- **AND** user has not already registered
- **THEN** system SHALL:
  - Create LotteryEntry with status PENDING
  - Calculate and store priority_score
  - Return entry details with score breakdown

#### Scenario: Registration Outside Window

- **WHEN** user attempts registration
- **AND** current date is before registration_start_date
- **THEN** system SHALL reject with "ثبت‌نام هنوز شروع نشده است"

#### Scenario: Registration After Deadline

- **WHEN** user attempts registration
- **AND** current date is after registration_end_date
- **THEN** system SHALL reject with "مهلت ثبت‌نام پایان یافته است"

#### Scenario: Duplicate Registration Prevention

- **WHEN** user attempts to register
- **AND** user already has entry in this lottery
- **THEN** system SHALL reject with "شما قبلاً در این قرعه‌کشی ثبت‌نام کرده‌اید"

---

### Requirement: Family Information

سیستم باید اطلاعات خانوار را دریافت و اعتبارسنجی کند.

#### Scenario: Valid Family Count

- **WHEN** user provides family_count
- **THEN** family_count SHALL be between 1 and 10
- **AND** SHALL not exceed personnel's registered family_count

#### Scenario: Guest Details

- **WHEN** user provides guest information
- **THEN** each guest SHALL have:
  - name (required): نام و نام خانوادگی
  - relation (required): نسبت (همسر، فرزند، پدر، مادر، ...)
  - national_code (optional): کد ملی
- **AND** number of guests SHALL equal family_count - 1

#### Scenario: Invalid Guest Data

- **WHEN** guest data is incomplete or invalid
- **THEN** system SHALL return validation errors
- **AND** registration SHALL fail

---

### Requirement: Unit Type Preference

سیستم باید ترجیحات نوع واحد را ثبت کند.

#### Scenario: Set Unit Preferences

- **WHEN** user provides preferred_unit_types
- **THEN** system SHALL store array of preferences
- **AND** valid types are: room, suite, villa
- **AND** preferences are optional

---

### Requirement: Registration Cancellation

کاربران باید بتوانند قبل از قرعه‌کشی انصراف دهند.

#### Scenario: Cancel Before Draw

- **WHEN** user cancels registration
- **AND** lottery status is OPEN or CLOSED
- **AND** lottery has not been drawn
- **THEN** system SHALL:
  - Update entry status to CANCELLED
  - Return success confirmation

#### Scenario: Cancel After Draw

- **WHEN** user attempts to cancel
- **AND** lottery has been drawn
- **THEN** system SHALL reject with "امکان انصراف پس از قرعه‌کشی وجود ندارد"

---

### Requirement: Entry Status Tracking

سیستم باید وضعیت ثبت‌نام را ردیابی کند.

#### Scenario: View Entry Status

- **WHEN** user queries their entry
- **THEN** system SHALL return:
  - Current status (pending, won, waitlist, approved, rejected)
  - Priority score with breakdown
  - Rank (after draw)
  - Family details submitted
  - Registration timestamp

#### Scenario: Entry Status History

- **WHEN** entry status changes
- **THEN** system SHALL log:
  - Previous status
  - New status
  - Timestamp
  - Actor (user/system/admin)

---

### Requirement: Three-Year Rule Check

سیستم باید واجد شرایط بودن برای نرخ بانکی را بررسی کند.

#### Scenario: Display Eligibility Status

- **WHEN** user views lottery details
- **THEN** system SHALL display:
  - Whether user is eligible for bank rate
  - Days since last use of this center
  - Days remaining until eligibility (if not eligible)

#### Scenario: Three-Year Rule Explanation

- **WHEN** user is not eligible for bank rate
- **THEN** system SHALL explain:
  - "شما در تاریخ X/X/XXXX از این مرکز استفاده کرده‌اید"
  - "برای استفاده با نرخ بانکی باید ۳ سال از آخرین استفاده گذشته باشد"
  - "شما می‌توانید با نرخ آزاد (بانکی) ثبت‌نام کنید"
