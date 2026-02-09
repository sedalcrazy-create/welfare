# Lottery Lifecycle - چرخه عمر قرعه‌کشی

## ADDED Requirements

### Requirement: Lottery Status Workflow

سیستم باید از یک چرخه عمر مشخص برای هر قرعه‌کشی پشتیبانی کند.

```
DRAFT → OPEN → CLOSED → DRAWN → APPROVAL → COMPLETED
                                    ↘ CANCELLED
```

| Status | Persian | Description |
|--------|---------|-------------|
| `draft` | پیش‌نویس | ایجاد اولیه، هنوز فعال نشده |
| `open` | باز | ثبت‌نام فعال است |
| `closed` | بسته | ثبت‌نام بسته، در انتظار قرعه‌کشی |
| `drawn` | قرعه‌کشی شده | برندگان مشخص شدند |
| `approval` | تأیید استانی | در انتظار تأیید مدیران استانی |
| `completed` | تکمیل شده | رزروها نهایی شدند |
| `cancelled` | لغو شده | قرعه‌کشی لغو شد |

#### Scenario: Status Transition Draft to Open

- **WHEN** admin changes lottery status from DRAFT to OPEN
- **AND** registration_start_date has passed
- **THEN** system SHALL update status to OPEN
- **AND** users SHALL be able to register

#### Scenario: Status Transition Open to Closed

- **WHEN** registration_end_date passes
- **OR** admin manually closes registration
- **THEN** system SHALL update status to CLOSED
- **AND** no new registrations SHALL be accepted

#### Scenario: Status Transition Closed to Drawn

- **WHEN** admin executes lottery draw
- **AND** status is CLOSED
- **AND** draw_date has passed
- **THEN** system SHALL execute draw algorithm
- **AND** update status to DRAWN

#### Scenario: Prevent Invalid Status Transitions

- **WHEN** admin attempts invalid status change (e.g., DRAFT to DRAWN)
- **THEN** system SHALL reject the request
- **AND** return appropriate error message

---

### Requirement: Lottery Creation

سیستم باید امکان ایجاد قرعه‌کشی جدید با اطلاعات کامل را فراهم کند.

#### Scenario: Create Lottery with Valid Data

- **WHEN** admin creates lottery with:
  - period_id (دوره اقامت)
  - title, description
  - registration_start_date
  - registration_end_date
  - draw_date
  - algorithm (weighted_random | priority_based)
- **AND** period is not already assigned to another lottery
- **THEN** system SHALL create lottery with status DRAFT

#### Scenario: Prevent Duplicate Period Assignment

- **WHEN** admin creates lottery for a period
- **AND** period already has an active lottery
- **THEN** system SHALL reject creation
- **AND** return error "این دوره قبلاً در قرعه‌کشی دیگری ثبت شده است"

---

### Requirement: Lottery Deletion

سیستم باید امکان حذف قرعه‌کشی‌های بدون شرکت‌کننده را فراهم کند.

#### Scenario: Delete Lottery Without Entries

- **WHEN** admin deletes lottery
- **AND** lottery has no entries
- **THEN** system SHALL delete the lottery

#### Scenario: Prevent Deletion with Entries

- **WHEN** admin attempts to delete lottery with entries
- **THEN** system SHALL reject deletion
- **AND** return error "قرعه‌کشی دارای شرکت‌کننده است و قابل حذف نیست"

---

### Requirement: Lottery Cancellation

سیستم باید امکان لغو قرعه‌کشی را فراهم کند.

#### Scenario: Cancel Lottery Before Draw

- **WHEN** admin cancels lottery
- **AND** status is DRAFT, OPEN, or CLOSED
- **THEN** system SHALL update status to CANCELLED
- **AND** all entries SHALL be marked as CANCELLED

#### Scenario: Prevent Cancellation After Draw

- **WHEN** admin attempts to cancel lottery after draw
- **THEN** system SHALL reject cancellation
- **AND** return appropriate error message
