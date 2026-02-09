# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**سامانه جامع مدیریت مراکز رفاهی بانک ملی ایران** - A Laravel 11 application for managing welfare centers reservation and lottery system. The system handles 3 welfare centers (Mashhad, Babolsar, Chadegan) with a total of 426 units and 1,781 beds for ~70,000 personnel.

### Key Features
- Provincial quota distribution (37 units: 31 provinces + 6 Tehran offices)
- Fair lottery system with priority scoring
- Provincial management approval workflow
- Bale mini-app integration for end users
- Season-based pricing and discounts

## Development Commands

### Docker (Primary Development Environment)
```bash
# Build and start (web accessible at http://localhost:8080)
docker-compose up -d --build

# Install dependencies
docker-compose exec app composer install

# Run migrations with seed data
docker-compose exec app php artisan migrate --seed

# Generate app key
docker-compose exec app php artisan key:generate

# View logs
docker-compose logs -f app

# Enter container shell
docker-compose exec app bash
```

### Docker Services
- **welfare_app**: PHP-FPM application
- **welfare_nginx**: Web server (port 8080)
- **welfare_postgres**: PostgreSQL (port 5433 → 5432)
- **welfare_redis**: Redis cache/queue (port 6380 → 6379)
- **welfare_queue**: Queue worker (auto-running)
- **welfare_scheduler**: Task scheduler (auto-running)

### Code Style & Testing
```bash
./vendor/bin/pint              # Code formatting (Laravel Pint)
php artisan test               # Run tests
php artisan test --filter=Name # Run specific test
```

## Architecture

### Domain Model Hierarchy
```
Province (استان/اداره امور)
└── Personnel (پرسنل) [family stored as JSON]

Center (مرکز رفاهی)
├── Unit (واحد/اتاق/ویلا)
├── Season (فصل)
└── Period (دوره اقامت)
    └── Lottery (قرعه‌کشی)
        └── LotteryEntry (شرکت‌کننده)
            └── Reservation (رزرو)
                └── UsageHistory (سابقه استفاده)
```

### Three Welfare Centers

| Center | City | Type | Units | Beds | Stay Duration |
|--------|------|------|-------|------|---------------|
| زائرسرای مشهد | Mashhad | Religious | 227 | 1,029 | 5 nights |
| متل بابلسر | Babolsar | Beach | 165 | 626 | 4 nights |
| موتل چادگان | Chadegan | Mountain | 34 | 126 | 3 nights |

### Key Models (`app/Models/`)

| Model | Key Fields | Notes |
|-------|------------|-------|
| Province | name, code, personnel_count, quota_ratio, is_tehran | 37 provinces/offices |
| Personnel | employee_code, national_code, is_isargar, service_years, family_count | Bank employees |
| Center | name, city, type (religious/beach/mountain), stay_duration | 3 welfare centers |
| Unit | number, bed_count, type (room/suite/villa), amenities (JSON) | 426 total units |
| Season | type (golden_peak/peak/mid_season/off_peak/super_off_peak), discount_rate | Per center |
| Period | start_date, end_date, capacity, status | Stay periods |
| Lottery | status (draft/open/closed/drawn/completed), draw_date, algorithm | Lottery events |
| LotteryEntry | priority_score, status (pending/won/waitlist/approved/rejected), rank | Participants |
| Reservation | tariff_type (bank_rate/free_bank_rate/free_non_bank_rate), guests (JSON) | Final bookings |
| UsageHistory | check_in_date, check_out_date | For 3-year rule checks |

### Key Services (`app/Services/`)

- **LotteryService** - Core lottery algorithm: `draw()`, `assignUnits()`, `promoteFromWaitlist()`
- **QuotaService** - Provincial quota calculation based on personnel count ratios
- **PriorityScoreService** - Priority scoring algorithm implementation

### Priority Score Algorithm
```php
score = 100 (base)
    + (days_since_last_use × 0.1)
    - (usage_count × 5)
    + (service_years × 0.5)
    + family_match_bonus (10)
    + random(0, 15)
    + isargar_bonus (30)
    + never_used_bonus (50)
```

### User Roles (Spatie Permission)

| Role | Persian | Access |
|------|---------|--------|
| super_admin | مدیر سیستم | All |
| admin | ادمین اداره کل | Centers, Lotteries, Reports |
| provincial_admin | مدیر استانی | Approve/Reject, Provincial Reports |
| operator | اپراتور | View, Data Entry |
| user | همکار | Register, View Results |

## Configuration

All business rules are configurable in `config/welfare.php`:
- `three_year_rule_days` - Days required between center uses (default: 1095)
- `priority_score.*` - All scoring algorithm parameters
- `tariffs.*` - Accommodation and meal pricing by tariff type
- `centers.*` - Per-center settings (stay duration, active periods, check-in times)
- `seasons.*` - Season type definitions and discounts

## API Structure (`routes/api.php`)

### Public
- `POST /api/auth/bale-verify` - Bale authentication
- `POST /api/auth/login` - Standard login

### User (auth:sanctum)
- `GET /api/centers`, `/api/lotteries`, `/api/user/*`
- `POST /api/lotteries/{id}/enter`

### Provincial Admin (role:provincial_admin)
- `GET /api/provincial/quota`, `/entries`, `/entries/pending`
- `POST /api/provincial/entries/{id}/approve|reject`

### Admin (role:admin|super_admin)
- `POST /api/admin/lotteries/{id}/draw`
- `GET /api/admin/reports/occupancy|provincial|fairness`

## Admin Panel Routes (`routes/web.php`)

Resource controllers for: centers, units, periods, lotteries, reservations, personnel, provinces
Reports at: `/reports/occupancy`, `/reports/provincial`, `/reports/financial`, `/reports/fairness`

## Business Rules

### 3-Year Rule (قانون ۳ سال)
- Personnel can use each center with bank rate only if 3+ years since last use of THAT center
- Each center quota is separate

### Provincial Approval Workflow
1. Personnel wins lottery → status: `won`
2. Provincial admin approves → status: `approved`
3. Unit assigned → Reservation created
4. If rejected → `promoteFromWaitlist()` promotes next person from SAME province

### Capacity Matching
- Unit assignment prioritizes: exact match → +1 bed → -1 bed (if family > 2) → any larger

### Tariff Types
- **Bank Rate**: 2,000,000 Rial/night for whole family (3-year eligible)
- **Free Bank Rate**: 1,950,000 Rial/night per person (non-eligible bank employees)
- **Free Non-Bank Rate**: 3,900,000 Rial/night per person

## Database

- **PostgreSQL 16** - Database name: `welfare_system`
- Jalali dates stored as strings (YYYYMMDD format)
- JSON columns: amenities, guests, preferred_units

## Seeder Order

Run order (foreign key constraints):
1. RolePermissionSeeder
2. ProvinceSeeder (37 records)
3. CenterSeeder (3 records)
4. UnitSeeder (426 records)
5. UserSeeder

Default login: `admin@bankmelli.ir` / `password`

## Scheduled Tasks (`routes/console.php`)

- `lottery:close-registration` - Daily 23:59
- `lottery:auto-draw` - Daily 10:00
- `lottery:send-reminders` - Daily 09:00
- `lottery:cleanup-expired` - Daily

Note: Console commands in `app/Console/Commands/` need to be created to support these schedules.
