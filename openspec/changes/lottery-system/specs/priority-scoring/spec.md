# Priority Scoring - الگوریتم امتیازدهی اولویت

## ADDED Requirements

### Requirement: Priority Score Calculation

سیستم باید امتیاز اولویت هر شرکت‌کننده را بر اساس فرمول چندعاملی محاسبه کند.

**فرمول اصلی:**
```
score = base_score (100)
    + (days_since_last_use × 0.1)
    - (usage_count × 5)
    + (service_years × 0.5)
    + family_match_bonus
    + isargar_bonus (30)
    + never_used_bonus (50)
```

| Component | Value | Description |
|-----------|-------|-------------|
| Base Score | 100 | امتیاز پایه برای همه |
| Days Since Last Use | days × 0.1 | هر ۱۰ روز = ۱ امتیاز |
| Service Years | years × 0.5 | هر ۲ سال = ۱ امتیاز |
| Usage Penalty | count × -5 | کاهش برای استفاده امسال |
| Family Match | 5-15 | بونوس تناسب خانوار |
| Isargar Bonus | 30 | ایثارگران و جانبازان |
| Never Used Bonus | 50 | اگر هرگز استفاده نکرده |

#### Scenario: Calculate Score for New Personnel

- **WHEN** personnel with 10 years service enters lottery
- **AND** has never used any center
- **AND** is not isargar
- **AND** family count is 3
- **THEN** score SHALL be:
  - Base: 100
  - Service years: 10 × 0.5 = 5
  - Never used: 50
  - Family match (≤3): 15
  - **Total: 170**

#### Scenario: Calculate Score for Isargar

- **WHEN** isargar personnel enters lottery
- **THEN** score SHALL include isargar_bonus of 30 points
- **AND** isargar status SHALL be verified from personnel record

#### Scenario: Calculate Score with Usage Penalty

- **WHEN** personnel has used a center 2 times this year
- **THEN** score SHALL deduct 2 × 5 = 10 points
- **AND** usage count SHALL be calculated from UsageHistory for current Jalali year

#### Scenario: Calculate Days Since Last Use

- **WHEN** personnel last used center 365 days ago
- **THEN** days_since_last_use_bonus SHALL be 365 × 0.1 = 36.5 points

---

### Requirement: Family Match Bonus Calculation

سیستم باید بونوس تناسب خانوار را بر اساس اندازه خانوار محاسبه کند.

| Family Size | Multiplier | Bonus |
|-------------|------------|-------|
| ≤ 3 نفر | 1.5× | 15 |
| 4-5 نفر | 1.0× | 10 |
| > 5 نفر | 0.5× | 5 |

#### Scenario: Small Family Bonus

- **WHEN** family_count is 3 or less
- **THEN** family_match_bonus SHALL be 15 points (base 10 × 1.5)

#### Scenario: Medium Family Bonus

- **WHEN** family_count is 4 or 5
- **THEN** family_match_bonus SHALL be 10 points (base 10 × 1.0)

#### Scenario: Large Family Bonus

- **WHEN** family_count is greater than 5
- **THEN** family_match_bonus SHALL be 5 points (base 10 × 0.5)

---

### Requirement: Score Breakdown Transparency

سیستم باید جزئیات امتیاز را برای شفافیت به کاربر نمایش دهد.

#### Scenario: Display Score Breakdown

- **WHEN** user views their lottery entry
- **THEN** system SHALL display:
  - Total score
  - Base score component
  - Service years component
  - Days since last use component
  - Usage penalty component
  - Family match bonus
  - Special bonuses (isargar, never_used)

#### Scenario: Score Recalculation

- **WHEN** lottery draw is executed
- **THEN** scores SHALL be recalculated for all entries
- **AND** random factor (0-15) SHALL be added during sorting

---

### Requirement: Minimum Score Guarantee

سیستم باید تضمین کند که امتیاز هرگز منفی نشود.

#### Scenario: Prevent Negative Score

- **WHEN** calculated score becomes negative due to penalties
- **THEN** final score SHALL be set to 0 (minimum)
