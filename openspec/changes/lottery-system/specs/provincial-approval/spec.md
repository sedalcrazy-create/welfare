# Provincial Approval - تأیید استانی

## ADDED Requirements

### Requirement: Provincial Admin Approval Workflow

مدیران استانی باید برندگان استان خود را تأیید یا رد کنند.

#### Scenario: Approve Winning Entry

- **WHEN** provincial admin approves entry
- **AND** entry belongs to admin's province
- **AND** entry status is WON
- **THEN** system SHALL:
  - Update entry status to APPROVED
  - Record approved_by (admin user ID)
  - Record approved_at (timestamp)
  - Queue notification to personnel

#### Scenario: Reject Winning Entry

- **WHEN** provincial admin rejects entry
- **AND** entry belongs to admin's province
- **AND** entry status is WON
- **THEN** system SHALL:
  - Update entry status to REJECTED
  - Record rejection_reason (required)
  - Trigger waitlist promotion
  - Notify personnel of rejection with reason

#### Scenario: Cross-Province Access Denied

- **WHEN** provincial admin attempts to approve/reject entry
- **AND** entry belongs to different province
- **THEN** system SHALL reject request
- **AND** return 403 Forbidden error

---

### Requirement: Rejection Reason Requirement

سیستم باید دلیل رد را الزامی کند.

#### Scenario: Require Rejection Reason

- **WHEN** provincial admin rejects entry
- **AND** rejection_reason is empty or missing
- **THEN** system SHALL reject request
- **AND** return validation error "دلیل رد الزامی است"

#### Scenario: Valid Rejection Reasons

- **WHEN** provincial admin provides rejection reason
- **THEN** reason SHALL be one of:
  - "عدم تأیید مدارک"
  - "عدم واجد شرایط بودن"
  - "درخواست انصراف همکار"
  - "سایر" (with additional notes)

---

### Requirement: Approval Deadline

سیستم باید مهلت تأیید را مدیریت کند.

#### Scenario: Approval Window

- **WHEN** lottery is drawn
- **THEN** provincial admins SHALL have X days to approve/reject
- **AND** countdown SHALL be displayed in admin panel

#### Scenario: Expired Approval Window

- **WHEN** approval deadline passes
- **AND** entry is still WON (not approved/rejected)
- **THEN** system SHALL:
  - Auto-mark entry as EXPIRED
  - Trigger waitlist promotion
  - Notify personnel and provincial admin

---

### Requirement: Pending Approval Dashboard

سیستم باید داشبورد تأیید‌های معلق را ارائه دهد.

#### Scenario: View Pending Approvals

- **WHEN** provincial admin accesses dashboard
- **THEN** system SHALL display:
  - List of WON entries pending approval
  - Personnel details (name, employee code)
  - Family count and guest details
  - Priority score breakdown
  - Days remaining for approval

#### Scenario: Filter by Status

- **WHEN** admin filters pending approvals
- **THEN** filters SHALL include:
  - All pending
  - Approved
  - Rejected
  - Expired

---

### Requirement: Bulk Operations

سیستم باید عملیات گروهی را پشتیبانی کند.

#### Scenario: Bulk Approve

- **WHEN** provincial admin selects multiple entries
- **AND** requests bulk approval
- **THEN** system SHALL approve all selected entries
- **AND** use same approved_by and approved_at for batch

#### Scenario: Bulk Approve Validation

- **WHEN** bulk approve includes entries from other provinces
- **THEN** system SHALL:
  - Approve only valid entries
  - Skip invalid entries
  - Return summary of results
