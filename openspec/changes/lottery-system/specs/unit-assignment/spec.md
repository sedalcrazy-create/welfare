# Unit Assignment - تخصیص واحد اقامتی

## ADDED Requirements

### Requirement: Intelligent Unit Assignment

سیستم باید واحدها را بر اساس تعداد خانوار به صورت هوشمند تخصیص دهد.

**اولویت تخصیص:**
1. تطابق دقیق (ظرفیت = تعداد خانوار)
2. +۱ تخت (ظرفیت = خانوار + ۱)
3. -۱ تخت (ظرفیت = خانوار - ۱) - فقط برای خانوار > ۲
4. هر واحد بزرگتر (fallback)

#### Scenario: Exact Capacity Match

- **WHEN** family_count is 4
- **AND** unit with bed_count 4 is available
- **THEN** system SHALL assign that unit (priority 1)
- **AND** capacity waste SHALL be 0

#### Scenario: Plus One Bed Match

- **WHEN** family_count is 3
- **AND** no unit with bed_count 3 available
- **AND** unit with bed_count 4 is available
- **THEN** system SHALL assign 4-bed unit (priority 2)
- **AND** capacity waste SHALL be 1

#### Scenario: Minus One Bed Match

- **WHEN** family_count is 4
- **AND** no units with bed_count 4 or 5 available
- **AND** unit with bed_count 3 is available
- **THEN** system SHALL assign 3-bed unit (priority 3)
- **AND** extra mattress note SHALL be added
- **AND** this SHALL only apply when family_count > 2

#### Scenario: Fallback to Any Larger

- **WHEN** no suitable unit found with above rules
- **AND** larger unit is available
- **THEN** system SHALL assign smallest available larger unit

---

### Requirement: Reservation Creation

سیستم باید رزرو را پس از تخصیص واحد ایجاد کند.

#### Scenario: Create Reservation

- **WHEN** unit is assigned to approved entry
- **THEN** system SHALL create Reservation with:
  - personnel_id from entry
  - unit_id assigned
  - period_id from lottery
  - tariff_type (bank_rate or free_bank_rate)
  - guests array from entry
  - status: CONFIRMED

#### Scenario: Tariff Type Determination

- **WHEN** creating reservation
- **AND** personnel is eligible for bank rate (3-year rule)
- **THEN** tariff_type SHALL be `bank_rate`
- **AND** pricing SHALL be per-family

#### Scenario: Non-Eligible Tariff Type

- **WHEN** creating reservation
- **AND** personnel is NOT eligible for bank rate
- **THEN** tariff_type SHALL be `free_bank_rate`
- **AND** pricing SHALL be per-person

---

### Requirement: Unit Availability Tracking

سیستم باید در دسترس بودن واحدها را ردیابی کند.

#### Scenario: Mark Unit as Assigned

- **WHEN** unit is assigned to reservation
- **THEN** unit SHALL be removed from available pool
- **AND** unit status SHALL be RESERVED for that period

#### Scenario: Check Unit Availability

- **WHEN** assigning units
- **THEN** system SHALL only consider units that are:
  - Active (unit.is_active = true)
  - Not already reserved for the period
  - Belong to correct center

---

### Requirement: Assignment Statistics

سیستم باید آمار تخصیص را گزارش دهد.

#### Scenario: Assignment Summary

- **WHEN** unit assignment completes
- **THEN** system SHALL return:
  - Total approved entries
  - Successfully assigned count
  - Failed assignments (with reasons)
  - Remaining available units

#### Scenario: Unassigned Entries

- **WHEN** no suitable unit found for entry
- **THEN** entry SHALL remain in APPROVED status
- **AND** warning SHALL be logged
- **AND** admin SHALL be notified

---

### Requirement: Assignment Constraints

سیستم باید محدودیت‌های تخصیص را اعمال کند.

#### Scenario: Prevent Double Assignment

- **WHEN** personnel already has reservation for period
- **THEN** system SHALL skip assignment
- **AND** log duplicate warning

#### Scenario: Respect Unit Type Preferences

- **WHEN** entry has preferred_unit_types
- **THEN** system SHALL prefer matching types
- **AND** fall back to any type if preference unavailable

#### Scenario: Center-Specific Assignment

- **WHEN** assigning units for lottery
- **THEN** units SHALL only be from lottery's center
- **AND** cross-center assignment SHALL be prevented
