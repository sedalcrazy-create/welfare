# Waitlist Promotion - مدیریت لیست انتظار

## ADDED Requirements

### Requirement: Automatic Waitlist Promotion

سیستم باید هنگام رد شدن یک برنده، نفر بعدی از لیست انتظار همان استان را ارتقا دهد.

#### Scenario: Promote Next Person on Rejection

- **WHEN** provincial admin rejects a winning entry
- **AND** waitlist has entries from same province
- **THEN** system SHALL:
  1. Find next waitlisted entry from same province (lowest rank)
  2. Update entry status from WAITLIST to WON
  3. Preserve rank from rejected entry
  4. Notify promoted personnel

#### Scenario: No Waitlist Available

- **WHEN** provincial admin rejects a winning entry
- **AND** no entries exist in waitlist for that province
- **THEN** system SHALL:
  - Mark spot as unfilled
  - Log warning for admin review
  - NOT affect other provinces' quotas

#### Scenario: Maintain Provincial Quota Integrity

- **WHEN** promotion occurs
- **THEN** total winners per province SHALL remain equal to quota
- **AND** promoted entry SHALL count against province quota

---

### Requirement: Waitlist Ordering

سیستم باید لیست انتظار را بر اساس رتبه مرتب نگه دارد.

#### Scenario: Waitlist Rank Assignment

- **WHEN** draw completes
- **AND** entries are marked as WAITLIST
- **THEN** each waitlist entry SHALL have rank assigned
- **AND** ranks SHALL continue after winners (e.g., if 5 winners, waitlist starts at 6)
- **AND** ranks SHALL be per-province

#### Scenario: Promotion Order

- **WHEN** multiple promotions needed
- **THEN** promotions SHALL occur in rank order (lowest rank first)
- **AND** each promotion SHALL use next available waitlisted person

---

### Requirement: Waitlist Expiration

سیستم باید لیست انتظار را پس از تکمیل قرعه‌کشی منقضی کند.

#### Scenario: Expire Waitlist on Completion

- **WHEN** lottery status changes to COMPLETED
- **THEN** all WAITLIST entries SHALL be marked as EXPIRED
- **AND** notifications SHALL be sent to expired entries

#### Scenario: Waitlist Validity Period

- **WHEN** lottery is in APPROVAL status
- **THEN** waitlist SHALL remain active
- **AND** promotions SHALL be possible until lottery completion

---

### Requirement: Promotion Audit Trail

سیستم باید تاریخچه ارتقاها را برای حسابرسی ثبت کند.

#### Scenario: Log Promotion Event

- **WHEN** waitlist promotion occurs
- **THEN** system SHALL log:
  - Original rejected entry ID
  - Promoted entry ID
  - Rejection reason
  - Timestamp
  - Admin who rejected

#### Scenario: Track Promotion Chain

- **WHEN** multiple sequential promotions occur (A rejected → B promoted → B rejected → C promoted)
- **THEN** system SHALL maintain complete promotion chain
- **AND** chain SHALL be queryable for audit
