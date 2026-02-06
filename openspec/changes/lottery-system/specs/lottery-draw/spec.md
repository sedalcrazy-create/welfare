# Lottery Draw - اجرای قرعه‌کشی

## ADDED Requirements

### Requirement: Draw Execution

سیستم باید قرعه‌کشی را به صورت تراکنشی و عادلانه اجرا کند.

#### Scenario: Execute Lottery Draw

- **WHEN** admin triggers draw
- **AND** lottery status is CLOSED
- **AND** draw_date has passed
- **THEN** system SHALL:
  1. Calculate provincial quotas
  2. Group entries by province
  3. Sort each province's entries by: `priority_score + random(0, 15)`
  4. Mark top N entries as WON (N = province quota)
  5. Mark remaining entries as WAITLIST
  6. Update lottery status to DRAWN
  7. Record draw statistics

#### Scenario: Atomic Draw Operation

- **WHEN** draw is executing
- **THEN** all operations SHALL be wrapped in database transaction
- **AND** if any step fails, entire draw SHALL be rolled back
- **AND** no partial results SHALL persist

#### Scenario: Draw Already Completed

- **WHEN** admin attempts to draw lottery
- **AND** lottery has already been drawn
- **THEN** system SHALL reject request
- **AND** return error "این قرعه‌کشی قبلاً انجام شده است"

---

### Requirement: Random Factor Application

سیستم باید عنصر تصادفی را برای جلوگیری از قطعیت نتایج اعمال کند.

#### Scenario: Add Random Factor During Sorting

- **WHEN** entries are sorted for draw
- **THEN** each entry SHALL receive random factor between 0 and 15
- **AND** sorting key SHALL be: `priority_score + random_factor`

#### Scenario: Random Factor Prevents Ties

- **WHEN** two entries have identical priority scores
- **THEN** random factor SHALL determine winner
- **AND** both entries have fair chance of winning

---

### Requirement: Provincial Winners Selection

سیستم باید برندگان هر استان را بر اساس سهمیه انتخاب کند.

#### Scenario: Select Winners Per Province

- **WHEN** province has 5 quota spots
- **AND** province has 20 entries
- **THEN** top 5 entries (by score + random) SHALL be marked WON
- **AND** remaining 15 entries SHALL be marked WAITLIST
- **AND** entries SHALL be assigned rank (1, 2, 3...)

#### Scenario: Province Has Fewer Entries Than Quota

- **WHEN** province has 3 entries
- **AND** province quota is 5 spots
- **THEN** all 3 entries SHALL be marked WON
- **AND** 2 spots SHALL remain unfilled from that province

---

### Requirement: Draw Statistics Recording

سیستم باید آمار قرعه‌کشی را ثبت کند.

#### Scenario: Record Draw Statistics

- **WHEN** draw completes successfully
- **THEN** system SHALL record:
  - `drawn_at`: timestamp of draw
  - `drawn_by`: user ID who executed draw
  - `total_participants`: count of all entries
  - `total_winners`: count of WON entries

#### Scenario: Draw Result Summary

- **WHEN** draw completes
- **THEN** system SHALL return:
  - success status
  - total participants count
  - winners count
  - waitlist count
  - per-province breakdown

---

### Requirement: Entry Status Update

سیستم باید وضعیت شرکت‌کنندگان را پس از قرعه‌کشی به‌روز کند.

#### Scenario: Winner Entry Status

- **WHEN** entry is selected as winner
- **THEN** entry status SHALL change from PENDING to WON
- **AND** entry rank SHALL be assigned (1 = first in province)

#### Scenario: Waitlist Entry Status

- **WHEN** entry is not selected as winner
- **THEN** entry status SHALL change from PENDING to WAITLIST
- **AND** entry rank SHALL be assigned (continues after winners)

#### Scenario: Notification After Draw

- **WHEN** draw completes
- **THEN** system SHALL queue notifications to:
  - Winners: "تبریک! شما در قرعه‌کشی برنده شدید"
  - Waitlist: "شما در لیست انتظار قرار گرفتید"
