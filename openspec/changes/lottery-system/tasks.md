# Lottery System Tasks - لیست وظایف پیاده‌سازی

## 1. Priority Score Service

- [ ] 1.1 Create PriorityScoreService class with calculate() method
- [ ] 1.2 Implement base score calculation (100 points)
- [ ] 1.3 Implement days_since_last_use bonus (days × 0.1)
- [ ] 1.4 Implement service_years bonus (years × 0.5)
- [ ] 1.5 Implement usage_penalty calculation (count × -5)
- [ ] 1.6 Implement family_match_bonus with size multipliers
- [ ] 1.7 Implement isargar_bonus (30 points)
- [ ] 1.8 Implement never_used_bonus (50 points)
- [ ] 1.9 Add getScoreBreakdown() method for transparency
- [ ] 1.10 Add minimum score guarantee (never negative)
- [ ] 1.11 Write unit tests for all scoring scenarios

## 2. Quota Service

- [ ] 2.1 Create QuotaService class
- [ ] 2.2 Implement calculateQuotasForPeriod() method
- [ ] 2.3 Implement ratio-based quota distribution formula
- [ ] 2.4 Implement rounding adjustment algorithm
- [ ] 2.5 Add getQuotaSummary() method for reporting
- [ ] 2.6 Implement recalculateProvinceRatios()
- [ ] 2.7 Handle minimum 1 spot per province rule
- [ ] 2.8 Write unit tests for quota calculations

## 3. Lottery Model & Migrations

- [ ] 3.1 Create Lottery model with status enum
- [ ] 3.2 Add lottery migration (period_id, dates, status, algorithm)
- [ ] 3.3 Create LotteryEntry model with status enum
- [ ] 3.4 Add lottery_entries migration (lottery_id, personnel_id, score, rank)
- [ ] 3.5 Define model relationships (Lottery hasMany LotteryEntry)
- [ ] 3.6 Add scopes: active(), readyToDraw(), winners()
- [ ] 3.7 Implement status transition validation
- [ ] 3.8 Add database indexes for performance

## 4. Lottery Service - Draw

- [ ] 4.1 Create LotteryService class
- [ ] 4.2 Implement draw() method with transaction wrapper
- [ ] 4.3 Implement quota retrieval per province
- [ ] 4.4 Implement entry grouping by province
- [ ] 4.5 Implement sorting: priority_score + random(0,15)
- [ ] 4.6 Implement winners selection (top N per quota)
- [ ] 4.7 Implement waitlist assignment for non-winners
- [ ] 4.8 Implement rank assignment within province
- [ ] 4.9 Update lottery status and statistics
- [ ] 4.10 Add database locking to prevent concurrent draws
- [ ] 4.11 Write integration tests for draw scenarios

## 5. Waitlist Promotion

- [ ] 5.1 Implement promoteFromWaitlist() method
- [ ] 5.2 Find next waitlisted entry from same province
- [ ] 5.3 Update status from WAITLIST to WON
- [ ] 5.4 Preserve rank from rejected entry
- [ ] 5.5 Handle empty waitlist scenario
- [ ] 5.6 Log promotion events for audit
- [ ] 5.7 Write tests for promotion scenarios

## 6. Unit Assignment

- [ ] 6.1 Implement assignUnits() method
- [ ] 6.2 Implement findSuitableUnit() with capacity matching
- [ ] 6.3 Add priority: exact → +1 → -1 (if family>2) → any larger
- [ ] 6.4 Create Reservation on successful assignment
- [ ] 6.5 Implement tariff_type determination
- [ ] 6.6 Update unit availability tracking
- [ ] 6.7 Return assignment statistics
- [ ] 6.8 Write tests for unit matching algorithm

## 7. User API Endpoints

- [ ] 7.1 Create Api/LotteryController
- [ ] 7.2 Implement GET /lotteries (list active)
- [ ] 7.3 Implement GET /lotteries/{id} (details)
- [ ] 7.4 Implement POST /lotteries/{id}/enter (register)
- [ ] 7.5 Implement DELETE /lotteries/{id}/cancel
- [ ] 7.6 Implement GET /lotteries/{id}/my-entry
- [ ] 7.7 Implement GET /lotteries/{id}/results
- [ ] 7.8 Add validation: family_count, guests array
- [ ] 7.9 Add duplicate registration prevention
- [ ] 7.10 Write API tests

## 8. Admin API Endpoints

- [ ] 8.1 Create Admin/LotteryController
- [ ] 8.2 Implement POST /admin/lotteries (create)
- [ ] 8.3 Implement PATCH /admin/lotteries/{id}/status
- [ ] 8.4 Implement POST /admin/lotteries/{id}/draw
- [ ] 8.5 Add authorization: admin|super_admin
- [ ] 8.6 Write admin API tests

## 9. Provincial Admin Endpoints

- [ ] 9.1 Create Provincial/EntryController
- [ ] 9.2 Implement GET /provincial/entries (list)
- [ ] 9.3 Implement GET /provincial/entries/pending
- [ ] 9.4 Implement POST /provincial/entries/{id}/approve
- [ ] 9.5 Implement POST /provincial/entries/{id}/reject
- [ ] 9.6 Add province scope restriction
- [ ] 9.7 Require rejection_reason on reject
- [ ] 9.8 Trigger waitlist promotion on rejection
- [ ] 9.9 Write provincial API tests

## 10. Web Admin Panel

- [ ] 10.1 Create LotteryController for web
- [ ] 10.2 Implement index (list with filters)
- [ ] 10.3 Implement create/store forms
- [ ] 10.4 Implement show (details + entries)
- [ ] 10.5 Implement edit/update forms
- [ ] 10.6 Add draw button with confirmation
- [ ] 10.7 Add approval workflow UI
- [ ] 10.8 Create Blade views

## 11. Notifications

- [ ] 11.1 Create LotteryWinnerNotification
- [ ] 11.2 Create WaitlistNotification
- [ ] 11.3 Create ApprovalNotification
- [ ] 11.4 Create RejectionNotification
- [ ] 11.5 Create PromotionNotification
- [ ] 11.6 Configure notification channels (Bale, database)
- [ ] 11.7 Queue notifications for performance

## 12. Reports & Monitoring

- [ ] 12.1 Implement occupancy report
- [ ] 12.2 Implement provincial fairness report
- [ ] 12.3 Implement lottery statistics dashboard
- [ ] 12.4 Add logging for audit trail
- [ ] 12.5 Create admin dashboard widgets

## 13. Testing & QA

- [ ] 13.1 Write unit tests for all services
- [ ] 13.2 Write integration tests for lottery workflow
- [ ] 13.3 Write API tests for all endpoints
- [ ] 13.4 Perform load testing with 70,000 entries
- [ ] 13.5 UAT with provincial admins
- [ ] 13.6 Security testing (authorization checks)

## 14. Documentation

- [ ] 14.1 Document API endpoints (OpenAPI/Swagger)
- [ ] 14.2 Document scoring algorithm for users
- [ ] 14.3 Create admin user guide
- [ ] 14.4 Create provincial admin user guide
