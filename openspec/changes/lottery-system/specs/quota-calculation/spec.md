# Quota Calculation - محاسبه سهمیه استانی

## ADDED Requirements

### Requirement: Provincial Quota Distribution

سیستم باید سهمیه هر استان را متناسب با جمعیت پرسنل آن استان محاسبه کند.

**فرمول:**
```
province_quota = period_capacity × (province_personnel_count / total_personnel_count)
```

#### Scenario: Calculate Quota for Province

- **WHEN** period has capacity of 100 spots
- **AND** province has 7,000 personnel out of 70,000 total (10%)
- **THEN** province quota SHALL be 10 spots

#### Scenario: Handle Rounding Differences

- **WHEN** sum of rounded quotas differs from total capacity
- **THEN** system SHALL adjust quotas of larger provinces
- **AND** total assigned quotas SHALL equal exactly period capacity

#### Scenario: Minimum One Spot Per Province

- **WHEN** province has very small personnel count
- **AND** calculated quota rounds to zero
- **THEN** province SHALL receive minimum 1 spot (if personnel exists)

---

### Requirement: Quota Ratio Maintenance

سیستم باید نسبت سهمیه هر استان را به‌روز نگه دارد.

#### Scenario: Update Province Personnel Count

- **WHEN** province personnel count changes
- **THEN** system SHALL recalculate quota_ratio for ALL provinces
- **AND** formula: `quota_ratio = personnel_count / total_personnel`

#### Scenario: New Province Activation

- **WHEN** new province becomes active
- **THEN** system SHALL recalculate all quota ratios
- **AND** include new province in calculations

---

### Requirement: Quota Summary Report

سیستم باید گزارش خلاصه سهمیه را ارائه دهد.

#### Scenario: Generate Quota Summary

- **WHEN** admin requests quota summary for a period
- **THEN** system SHALL return:
  - Province name and code
  - Personnel count
  - Quota ratio (percentage)
  - Allocated spots
- **AND** results SHALL be sorted by quota descending

---

### Requirement: Tehran Office Handling

سیستم باید ۶ اداره امور تهران را به عنوان واحدهای مستقل مدیریت کند.

**ادارات امور تهران:**
1. اداره امور مرکزی
2. اداره امور شرق
3. اداره امور غرب
4. اداره امور شمال
5. اداره امور جنوب
6. اداره امور ستادی

#### Scenario: Separate Tehran Offices

- **WHEN** calculating quotas
- **THEN** each Tehran office SHALL have separate quota
- **AND** Tehran offices SHALL be marked with `is_tehran = true`
- **AND** total provinces SHALL be 37 (31 provinces + 6 Tehran offices)

---

### Requirement: Quota Validation

سیستم باید صحت محاسبات سهمیه را تأیید کند.

#### Scenario: Validate Total Quota Equals Capacity

- **WHEN** quotas are calculated for a period
- **THEN** sum of all provincial quotas SHALL equal period capacity exactly
- **AND** no quota SHALL be negative

#### Scenario: Handle Zero Capacity Period

- **WHEN** period has zero capacity
- **THEN** all provincial quotas SHALL be zero
- **AND** no lottery SHALL be allowed
