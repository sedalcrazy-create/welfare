# Letter Template - قالب معرفی‌نامه

## ADDED Requirements - الزامات جدید

### Requirement: Letter Content - محتوای معرفی‌نامه

معرفی‌نامه باید شامل اطلاعات کامل پرسنل و همراهان باشد.
The introduction letter SHALL contain complete personnel and companion information.

**Letter Fields / فیلدهای معرفی‌نامه:**

| Field / فیلد | Description / توضیح |
|--------------|---------------------|
| letter_number | شماره معرفی‌نامه |
| issue_date | تاریخ صدور (شمسی) |
| personnel_name | نام و نام خانوادگی پرسنل |
| employee_code | کد استخدامی |
| national_code | کد ملی پرسنل |
| province_name | نام استان/اداره امور |
| center_name | نام مرکز رفاهی |
| period_dates | تاریخ شروع و پایان نوبت |
| stay_duration | مدت اقامت (شب) |
| companion_count | تعداد همراهان |
| companions_list | لیست همراهان با نسبت |
| tariff_type | نوع تعرفه |
| notes | توضیحات |

#### Scenario: Generate Letter Content - تولید محتوای معرفی‌نامه

- **WHEN** letter is created
- **THEN** system SHALL populate all fields from:
  - Personnel record (name, codes)
  - Province record (name)
  - Period record (dates, center)
  - Input data (companions, notes)

---

### Requirement: Companions Table - جدول همراهان

معرفی‌نامه باید جدول همراهان را شامل شود.
The letter SHALL include companions table.

**Table Format / قالب جدول:**

| ردیف | نام و نام خانوادگی | کد ملی | نسبت |
|------|-------------------|--------|------|
| ۱ | فاطمه محمدی | ۰۰۱۲۳۴۵۶۷۸ | همسر |
| ۲ | علی محمدی | ۰۰۲۳۴۵۶۷۸۹ | فرزند |
| ۳ | زهرا محمدی | ۰۰۳۴۵۶۷۸۹۰ | فرزند |

#### Scenario: Display All Companions - نمایش همه همراهان

- **WHEN** letter is printed/viewed
- **THEN** all companions SHALL be listed in table
- **AND** row numbers SHALL be sequential
- **AND** personnel themselves SHALL NOT be in table (header only)

---

### Requirement: Print Format - قالب چاپ

سیستم باید معرفی‌نامه را با قالب قابل چاپ ارائه دهد.
The system SHALL provide printable letter format.

#### Scenario: Generate PDF - تولید PDF

- **WHEN** admin clicks "Print" or "Download PDF"
- **THEN** system SHALL generate PDF with:
  - Bank Melli logo / لوگوی بانک ملی
  - Official header / سربرگ رسمی
  - Letter content / محتوای معرفی‌نامه
  - Signature area / محل امضا
  - QR code for verification / کد QR برای استعلام

#### Scenario: Print Preview - پیش‌نمایش چاپ

- **WHEN** admin clicks "Preview"
- **THEN** system SHALL display letter in browser
- **AND** allow printing via browser print dialog

---

### Requirement: Letter Header - سربرگ معرفی‌نامه

معرفی‌نامه باید دارای سربرگ رسمی باشد.
The letter SHALL have official header.

**Header Content / محتوای سربرگ:**
```
بسمه تعالی
بانک ملی ایران
اداره کل رفاه و تعاون
معرفی‌نامه اقامت در مراکز رفاهی

شماره: {letter_number}
تاریخ: {issue_date}
```

#### Scenario: Center-Specific Header - سربرگ مختص مرکز

- **WHEN** generating letter for specific center
- **THEN** header MAY include center-specific info
- **AND** center logo if available

---

### Requirement: QR Code Verification - تأیید با کد QR

معرفی‌نامه باید دارای کد QR برای استعلام باشد.
The letter SHALL have QR code for verification.

#### Scenario: Generate Verification QR - تولید QR استعلام

- **WHEN** letter is generated
- **THEN** system SHALL create QR code
- **AND** QR SHALL contain: verification URL with letter_number
- **AND** scanning QR SHALL show letter validity

#### Scenario: Verify Letter - استعلام معرفی‌نامه

- **WHEN** operator scans QR or enters letter_number
- **THEN** system SHALL display:
  - Letter status (valid/cancelled)
  - Personnel name
  - Period dates
  - Companion count
  - Issue date and issuer

---

### Requirement: Letter Watermark - واترمارک معرفی‌نامه

معرفی‌نامه باید دارای واترمارک امنیتی باشد.
The letter SHALL have security watermark.

#### Scenario: Add Watermark - اضافه کردن واترمارک

- **WHEN** PDF is generated
- **THEN** system SHALL add watermark:
  - "بانک ملی ایران" diagonal text
  - Semi-transparent
  - Across entire page

#### Scenario: Cancelled Letter Watermark - واترمارک لغو شده

- **WHEN** cancelled letter is viewed/printed
- **THEN** system SHALL add red "لغو شده / CANCELLED" watermark
- **AND** original content SHALL still be visible
