# Letter Issuance - صدور معرفی‌نامه

## ADDED Requirements - الزامات جدید

### Requirement: Issue Introduction Letter - صدور معرفی‌نامه

ادمین استانی باید بتواند معرفی‌نامه برای پرسنل استان خود صادر کند.
Provincial admin SHALL be able to issue introduction letters for their province's personnel.

**Data Structure / ساختار داده:**
```
IntroductionLetter {
    id: int
    letter_number: string       // شماره معرفی‌نامه (auto-generated)
    province_id: int            // استان صادرکننده
    period_id: int              // نوبت اقامت
    center_id: int              // مرکز رفاهی
    personnel_id: int           // پرسنل
    employee_code: string       // کد استخدامی
    personnel_name: string      // نام پرسنل
    companions: json            // همراهان
    companion_count: int        // تعداد همراهان
    tariff_type: enum           // نوع تعرفه
    issued_by: int              // صادرکننده
    issued_at: timestamp        // تاریخ صدور
    status: enum                // وضعیت (active/cancelled)
    notes: text                 // توضیحات
}
```

#### Scenario: Issue Letter with Valid Quota - صدور با سهمیه معتبر

- **WHEN** provincial admin issues letter
- **AND** provides: personnel_id, period_id, center_id, companions
- **AND** province has remaining quota > 0
- **AND** period is not locked
- **THEN** system SHALL:
  - Create IntroductionLetter record
  - Generate unique letter_number
  - Increment province used_quota by 1
  - Return letter details

- **وقتی** ادمین استانی معرفی‌نامه صادر می‌کند
- **و** پرسنل، نوبت، مرکز و همراهان را وارد می‌کند
- **و** استان سهمیه باقیمانده دارد
- **و** نوبت قفل نشده
- **آنگاه** سیستم باید معرفی‌نامه را ایجاد و سهمیه مصرفی را افزایش دهد

#### Scenario: Reject When Quota Exhausted - رد در صورت اتمام سهمیه

- **WHEN** provincial admin issues letter
- **AND** province remaining quota = 0
- **THEN** system SHALL reject with error
- **AND** return "سهمیه استان شما برای این نوبت تمام شده است"

#### Scenario: Reject When Period Locked - رد در صورت قفل بودن

- **WHEN** provincial admin issues letter
- **AND** period starts in less than lock_days_before
- **THEN** system SHALL reject with error
- **AND** return "امکان صدور معرفی‌نامه برای این نوبت وجود ندارد (X روز مانده به شروع)"

---

### Requirement: Companion Information - اطلاعات همراهان

سیستم باید اطلاعات همراهان را ثبت کند.
The system SHALL record companion information.

**Companion Structure / ساختار همراه:**
```
Companion {
    name: string            // نام و نام خانوادگی
    national_code: string   // کد ملی
    relation: string        // نسبت (همسر، فرزند، پدر، مادر، ...)
    birth_date: date        // تاریخ تولد (اختیاری)
}
```

#### Scenario: Add Companions - افزودن همراهان

- **WHEN** admin provides companion list
- **THEN** each companion SHALL have: name, relation
- **AND** national_code is optional
- **AND** companion_count SHALL be calculated automatically

#### Scenario: Validate Companion Count - اعتبارسنجی تعداد همراهان

- **WHEN** companion_count > 10
- **THEN** system SHALL reject with error
- **AND** return "حداکثر تعداد همراهان ۱۰ نفر است"

---

### Requirement: Letter Number Generation - تولید شماره معرفی‌نامه

سیستم باید شماره یکتا برای هر معرفی‌نامه تولید کند.
The system SHALL generate unique letter numbers.

**Format / قالب:** `{YEAR}{CENTER_CODE}{PROVINCE_CODE}{SEQUENCE}`
**Example / مثال:** `1403-MSH-THR-00142`

#### Scenario: Generate Unique Number - تولید شماره یکتا

- **WHEN** letter is created
- **THEN** system SHALL generate letter_number
- **AND** number SHALL be unique across all letters
- **AND** sequence SHALL reset per year

---

### Requirement: Provincial Scope Restriction - محدودیت دامنه استانی

ادمین استانی فقط باید بتواند برای پرسنل استان خود معرفی‌نامه صادر کند.
Provincial admin SHALL only issue letters for their own province's personnel.

#### Scenario: Restrict to Own Province - محدودیت به استان خود

- **WHEN** provincial admin issues letter
- **AND** personnel belongs to different province
- **THEN** system SHALL reject with 403 Forbidden
- **AND** return "شما فقط می‌توانید برای پرسنل استان خود معرفی‌نامه صادر کنید"

#### Scenario: Super Admin Override - دسترسی مدیر سیستم

- **WHEN** super_admin issues letter
- **THEN** system SHALL allow for any province
- **AND** letter SHALL be marked with issued province

---

### Requirement: Letter Cancellation - لغو معرفی‌نامه

ادمین استانی باید بتواند معرفی‌نامه صادر شده را لغو کند.
Provincial admin SHALL be able to cancel issued letters.

#### Scenario: Cancel Letter - لغو معرفی‌نامه

- **WHEN** admin cancels letter
- **AND** letter status is active
- **AND** period has not started
- **THEN** system SHALL:
  - Update letter status to cancelled
  - Decrement province used_quota by 1
  - Record cancellation reason and timestamp

#### Scenario: Prevent Cancel After Period Start - جلوگیری از لغو پس از شروع

- **WHEN** admin tries to cancel letter
- **AND** period has started
- **THEN** system SHALL reject with error
- **AND** return "امکان لغو معرفی‌نامه پس از شروع نوبت وجود ندارد"
