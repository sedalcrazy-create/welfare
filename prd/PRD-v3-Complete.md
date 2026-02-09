# سند نیازمندی‌های محصول (PRD)
## سامانه جامع مدیریت مراکز رفاهی بانک ملی ایران

**نسخه:** 3.0 (نهایی)
**تاریخ:** دی ۱۴۰۴
**تهیه‌کننده:** اداره کل رفاه و درمان
**وضعیت:** آماده اجرا

---

## فهرست مطالب

1. [خلاصه اجرایی](#1-خلاصه-اجرایی)
2. [آمار و ارقام](#2-آمار-و-ارقام)
3. [مشخصات مراکز رفاهی](#3-مشخصات-مراکز-رفاهی)
4. [قوانین و مقررات](#4-قوانین-و-مقررات)
5. [فرآیند قرعه‌کشی](#5-فرآیند-قرعهکشی)
6. [معماری فنی](#6-معماری-فنی)
7. [مدل داده](#7-مدل-داده)
8. [API Endpoints](#8-api-endpoints)
9. [مینی‌اپ بله](#9-مینیاپ-بله)
10. [فازبندی پروژه](#10-فازبندی-پروژه)
11. [زیرساخت سرور](#11-زیرساخت-سرور)

---

## 1. خلاصه اجرایی

### 1.1 هدف پروژه
دیجیتالی‌سازی فرآیند توزیع سهمیه، قرعه‌کشی عادلانه و مدیریت رزرو سه مرکز رفاهی بانک ملی ایران با استفاده از مینی‌اپ بله.

### 1.2 مراکز تحت پوشش

| مرکز | شهر | نوع | تعداد واحد | تعداد تخت |
|------|-----|-----|------------|-----------|
| زائرسرای مشهد | مشهد | زیارتی | 227 | 1,029 |
| متل بابلسر | بابلسر | تفریحی-ساحلی | 165 | 626 |
| موتل چادگان | چادگان | تفریحی-کوهستان | 34 | 126 |
| **جمع** | - | - | **426** | **1,781** |

### 1.3 اجزای سامانه

```
┌─────────────────────────────────────────────────────────────┐
│                    سامانه مراکز رفاهی                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│   📱 مینی‌اپ بله          🖥️ پنل ادمین        🏢 پنل استانی  │
│   (همکاران)               (اداره کل)         (مدیران استان) │
│                                                              │
│   ┌─────────────────────────────────────────────────────┐   │
│   │                  Laravel Backend                     │   │
│   │           API + Queue + Scheduler                    │   │
│   └─────────────────────────────────────────────────────┘   │
│                            │                                 │
│   ┌────────────┐  ┌────────┴────────┐  ┌─────────────────┐  │
│   │   Redis    │  │   PostgreSQL    │  │   Bale Bot      │  │
│   │   Cache    │  │    Database     │  │   Notifications │  │
│   └────────────┘  └─────────────────┘  └─────────────────┘  │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. آمار و ارقام

### 2.1 جامعه هدف

| شاخص | تعداد |
|------|-------|
| کل پرسنل (شاغل + بازنشسته) | 70,203 نفر |
| خانواده بانکی (تخمینی) | ~200,000 نفر |
| تعداد استان‌ها | 31 |
| تعداد ادارات امور تهران | 6 |
| **جمع واحدهای توزیع سهمیه** | **37** |

### 2.2 ظرفیت سالانه

| مرکز | تخت | مدت اقامت | دوره در سال | ظرفیت سالانه |
|------|-----|-----------|-------------|--------------|
| مشهد | 1,029 | 5 شب | ~65 | ~67,000 نفر |
| بابلسر | 626 | 4 شب | ~75 | ~47,000 نفر |
| چادگان | 126 | 3 شب | ~55 | ~7,000 نفر |
| **جمع** | **1,781** | - | - | **~121,000 نفر** |

### 2.3 فرمول توزیع سهمیه

```
سهمیه_استان = (تعداد_پرسنل_استان / کل_پرسنل) × ظرفیت_دوره

مثال:
- خراسان رضوی: 2,000 پرسنل → 2,000/70,203 × 227 = 6.5 ≈ 7 واحد/دوره
- لرستان: 200 پرسنل → 200/70,203 × 227 = 0.65 ≈ 1 واحد/دوره
```

---

## 3. مشخصات مراکز رفاهی

### 3.1 زائرسرای مشهد مقدس

**تقویم فعالیت:**
- شروع: 28 اسفند
- پایان: آخر بهمن
- تعطیلی اورهال: اسفند
- مدت اقامت: 5 شب

**توزیع اتاق‌ها:**

| نوع اتاق | تعداد | جمع تخت | توضیحات |
|----------|-------|---------|---------|
| 2 تخته | 16 | 32 | - |
| 3 تخته | 31 | 93 | - |
| 4 تخته | 66 | 264 | بیشترین تعداد |
| 5 تخته | 58 | 290 | شامل سوئیت دوکله |
| 6 تخته | 49 | 294 | شامل مدیریتی دوخوابه |
| 8 تخته | 7 | 56 | مدیریتی سه خوابه |
| **جمع** | **227** | **1,029** | - |

**اتاق‌های خاص:**
- مدیریتی سه خوابه: 29 واحد
- سوئیت دوکله: 22 واحد
- مدیریتی دوخوابه وان‌دار: 7 واحد

**اتاق‌های مسدود (41 اتاق):**
- بازسازی: 28 اتاق
- آسایشگاه/درمانگاه: 7 اتاق
- نگهبانی/مدیریت: 6 اتاق

---

### 3.2 متل بابلسر

**تقویم فعالیت:**
- شروع: 28 اسفند
- پایان: نیمه بهمن
- تعطیلی اورهال: نیمه بهمن تا اسفند
- مدت اقامت: 4 شب

**توزیع واحدها:**

| نوع واحد | تعداد | جمع تخت |
|----------|-------|---------|
| 2 تخته | 20 | 40 |
| 3 تخته | 38 | 114 |
| 4 تخته | 77 | 308 |
| 5 تخته | 22 | 110 |
| 6 تخته | 6 | 36 |
| 9 تخته | 2 | 18 |
| **جمع** | **165** | **626** |

**انواع واحدها:**
- محوطه ویلایی همکف: 69
- آپارتمان: 42
- محوطه ویلایی طبقه بالا: 38
- سایر: 16

---

### 3.3 موتل چادگان

**تقویم فعالیت:**
- شروع: 28 اسفند
- پایان: 1 دی
- تعطیلی اورهال: دی تا اسفند
- مدت اقامت: 3 شب

**الگوی خاص (جمعه تعطیل):**
- دوره A: شنبه 17:00 → سه‌شنبه 09:00
- دوره B: سه‌شنبه 17:00 → جمعه 09:00

**توزیع ویلاها:**

| نوع ویلا | تعداد | جمع تخت |
|----------|-------|---------|
| 2 تخته (1 اتاقه) | 6 | 12 |
| 4 تخته (2 اتاقه) | 27 | 108 |
| 6 تخته (3 اتاقه) | 1 | 6 |
| **جمع** | **34** | **126** |

---

## 4. قوانین و مقررات

### 4.1 تعریف خانواده بانکی

اعضای مجاز برای استفاده با نرخ بانکی:
- ✅ همکار
- ✅ همسر (یا همسران)
- ✅ فرزندان تحت تکفل
- ✅ پدر و مادر همکار
- ✅ پدر و مادر همسر

> **نکته:** کودکان زیر 6 سال هزینه اقامت ندارند (فقط غذا)

### 4.2 قانون 3 سال

```
شرط استفاده با نرخ بانکی:
├── حداقل 3 سال از آخرین استفاده از همان مرکز گذشته باشد
├── سهمیه هر مرکز مجزا است
└── امکان استفاده از هر 3 مرکز در یک سال (در صورت رعایت شرط)
```

### 4.3 تعرفه‌ها

| نوع تعرفه | شرایط | اقامت (شبی) | صبحانه | ناهار | شام |
|-----------|-------|-------------|--------|-------|-----|
| **بانکی** | 3 سال + خانواده بانکی | 2,000,000 (کل) | 190,000 | 625,000 | 530,000 |
| **آزاد بانکی** | کمتر از 3 سال | 1,950,000 (نفری) | 520,000 | 1,690,000 | 1,430,000 |
| **آزاد غیربانکی** | میهمان خارج خانواده | 3,900,000 (نفری) | 600,000 | 1,950,000 | 1,650,000 |

### 4.4 تخفیفات ایثارگران

| دسته | تخفیف |
|------|-------|
| جانبازان، آزادگان، خانواده شهدا | 50% |
| جانبازان اعصاب و روان بالای 25% | رایگان (سالی 1 بار) |
| جانبازان 70%+ | رایگان (سالی 1 بار) |

### 4.5 فصل‌بندی

| فصل | بازه زمانی | میزان تقاضا |
|-----|-----------|-------------|
| **پیک طلایی** | 15 مرداد - 31 شهریور (بابلسر) | بسیار بالا |
| **پیک** | نوروز، تابستان، تعطیلات | بالا |
| **میان‌فصل** | 16 فروردین - 11 خرداد | متوسط |
| **غیرپیک** | مهر (چادگان)، بهمن (مشهد/بابلسر) | کم |
| **فوق‌العاده غیرپیک** | آذر/دی (مشهد/بابلسر)، آبان/آذر (چادگان) | بسیار کم |

---

## 5. فرآیند قرعه‌کشی

### 5.1 تقویم ماهانه

```
روز 1-14 ماه: ثبت درخواست رزرو برای دوره‌های ماه بعد
روز 15 ماه: اعلام نتایج قرعه‌کشی
```

### 5.2 مراحل قرعه‌کشی

```
┌─────────────────────────────────────────────────────────────┐
│                    فلوچارت قرعه‌کشی                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│   1️⃣ ثبت درخواست (مینی‌اپ بله)                              │
│      └── انتخاب مرکز، بازه زمانی، تعداد همراهان              │
│                         ↓                                    │
│   2️⃣ بررسی خودکار شرایط                                     │
│      └── قانون 3 سال، عضویت در خانواده بانکی                 │
│                         ↓                                    │
│   3️⃣ محاسبه امتیاز اولویت                                   │
│      └── سابقه عدم استفاده + سنوات + ...                     │
│                         ↓                                    │
│   4️⃣ اجرای قرعه‌کشی (روز 15)                                │
│      └── بر اساس سهمیه استان و امتیاز                        │
│                         ↓                                    │
│   5️⃣ تأیید مدیریت استانی ⚠️                                 │
│      └── بررسی تخلفات، تداخل مرخصی                           │
│                         ↓                                    │
│   6️⃣ جایگزینی (در صورت رد)                                  │
│      └── نفر بعدی از همان استان                              │
│                         ↓                                    │
│   7️⃣ اعلام نتایج                                            │
│      └── اعلان در بله + پیامک                                │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 5.3 الگوریتم امتیازدهی

```python
def calculate_priority_score(personnel):
    score = 100  # امتیاز پایه

    # سابقه عدم استفاده (هرچه بیشتر، بهتر)
    days_since_last = (today - personnel.last_stay_date).days
    score += days_since_last * 0.1  # هر روز 0.1 امتیاز

    # سابقه خدمت
    score += personnel.service_years * 0.5  # هر سال 0.5 امتیاز

    # کاهش برای استفاده مکرر در سال جاری
    score -= personnel.usage_count_this_year * 20

    # بونوس تطابق ظرفیت
    if personnel.family_count == unit.bed_count:
        score += 10  # تطابق کامل
    elif abs(personnel.family_count - unit.bed_count) == 1:
        score += 5   # تطابق نسبی

    # عامل تصادفی (برای عدالت)
    score += random.uniform(0, 15)

    # بونوس ایثارگران
    if personnel.is_isargar:
        score += 30

    # بونوس عدم استفاده قبلی
    if personnel.never_used:
        score += 50

    return score
```

### 5.4 تطبیق ظرفیت

| تعداد خانواده | اتاق پیشنهادی | قابل قبول |
|---------------|---------------|-----------|
| 2 نفر | 2 تخته | 2-3 تخته |
| 3-4 نفر | 4 تخته | 3-5 تخته |
| 5-6 نفر | 5-6 تخته | 5-6 تخته |
| 7+ نفر | 6 تخته | + تشک اضافه |

---

## 6. معماری فنی

### 6.1 Stack تکنولوژی

| لایه | تکنولوژی | نسخه | توضیحات |
|------|----------|------|---------|
| **Backend** | Laravel | 11.x | PHP 8.2+ |
| **Database** | PostgreSQL | 16 | موجود روی سرور |
| **Cache/Queue** | Redis | 7.x | Session, Cache, Queue |
| **Frontend (پنل)** | Vue 3 + Inertia.js | 3.x | SPA |
| **Frontend (مینی‌اپ)** | Vue 3 + Bale SDK | 3.x | PWA |
| **Container** | Docker Compose | 3.8 | Orchestration |
| **Web Server** | Nginx | 1.25 | Reverse Proxy |
| **Bot** | Laravel + Bale API | - | اعلان‌ها |

### 6.2 دیاگرام معماری

```
┌─────────────────────────────────────────────────────────────────────┐
│                         شبکه داخلی بانک                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│   ┌─────────────┐     ┌─────────────┐     ┌─────────────────────┐   │
│   │   Nginx     │────▶│   Laravel   │────▶│    PostgreSQL       │   │
│   │   :80/443   │     │   :8083     │     │    :5432            │   │
│   │  (موجود)    │     │   (جدید)    │     │    (موجود)          │   │
│   └─────────────┘     └──────┬──────┘     └─────────────────────┘   │
│                              │                                       │
│                        ┌─────▼─────┐     ┌─────────────────────┐    │
│                        │   Redis   │     │   Queue Worker      │    │
│                        │   :6379   │     │   (قرعه‌کشی)         │    │
│                        │   (جدید)  │     │   (جدید)            │    │
│                        └───────────┘     └─────────────────────┘    │
│                                                                      │
│   ┌─────────────────────────────────────────────────────────────┐   │
│   │                    Existing Services                         │   │
│   │  hotel:8082 | miniapbale:8081 | n8n:5678 | portainer:9443   │   │
│   └─────────────────────────────────────────────────────────────┘   │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              │ Bale API
                              ▼
                    ┌─────────────────────┐
                    │   مینی‌اپ بله       │
                    │   (کاربران نهایی)   │
                    └─────────────────────┘
```

### 6.3 Docker Compose

```yaml
version: '3.8'

services:
  # Welfare System App
  welfare-app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: welfare_app
    restart: unless-stopped
    ports:
      - "8083:80"
    volumes:
      - .:/var/www/html
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=production
      - DB_CONNECTION=pgsql
      - DB_HOST=miniapbale_postgres
      - DB_PORT=5432
      - DB_DATABASE=welfare_system
      - REDIS_HOST=welfare_redis
      - QUEUE_CONNECTION=redis
      - BALE_BOT_TOKEN=${BALE_BOT_TOKEN}
    depends_on:
      - welfare-redis
    networks:
      - miniapbale_network
      - welfare_network

  # Redis for Cache & Queue
  welfare-redis:
    image: redis:7-alpine
    container_name: welfare_redis
    restart: unless-stopped
    volumes:
      - redis_data:/data
    command: redis-server --appendonly yes
    networks:
      - welfare_network

  # Queue Worker
  welfare-worker:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: welfare_worker
    restart: unless-stopped
    command: php artisan queue:work redis --sleep=3 --tries=3 --max-jobs=1000
    volumes:
      - .:/var/www/html
    depends_on:
      - welfare-redis
    networks:
      - miniapbale_network
      - welfare_network

  # Scheduler (Cron)
  welfare-scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: welfare_scheduler
    restart: unless-stopped
    command: php artisan schedule:work
    volumes:
      - .:/var/www/html
    depends_on:
      - welfare-redis
    networks:
      - miniapbale_network
      - welfare_network

volumes:
  redis_data:

networks:
  welfare_network:
    driver: bridge
  miniapbale_network:
    external: true
```

---

## 7. مدل داده

### 7.1 ERD (Entity Relationship Diagram)

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│    provinces    │     │    centers      │     │     units       │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id              │     │ id              │     │ id              │
│ name            │     │ name            │     │ center_id (FK)  │
│ code            │     │ city            │     │ number          │
│ personnel_count │     │ type            │     │ name            │
│ is_tehran       │     │ bed_count       │     │ bed_count       │
│ created_at      │     │ unit_count      │     │ type            │
└────────┬────────┘     │ active_from     │     │ floor           │
         │              │ active_to       │     │ amenities (JSON)│
         │              │ stay_duration   │     │ is_management   │
         │              │ created_at      │     │ status          │
         │              └────────┬────────┘     │ created_at      │
         │                       │              └────────┬────────┘
         │                       │                       │
┌────────▼────────┐     ┌────────▼────────┐     ┌───────▼─────────┐
│   personnels    │     │    seasons      │     │  reservations   │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id              │     │ id              │     │ id              │
│ employee_code   │     │ center_id (FK)  │     │ personnel_id    │
│ national_code   │     │ name            │     │ unit_id (FK)    │
│ full_name       │     │ type            │     │ period_id (FK)  │
│ province_id(FK) │     │ start_date      │     │ guest_count     │
│ bale_user_id    │     │ end_date        │     │ guests (JSON)   │
│ phone           │     │ discount_rate   │     │ tariff_type     │
│ is_isargar      │     │ created_at      │     │ total_amount    │
│ isargar_type    │     └─────────────────┘     │ status          │
│ service_years   │                             │ check_in_at     │
│ family_count    │     ┌─────────────────┐     │ check_out_at    │
│ created_at      │     │    periods      │     │ approved_by     │
└────────┬────────┘     ├─────────────────┤     │ created_at      │
         │              │ id              │     └─────────────────┘
         │              │ center_id (FK)  │
         │              │ season_id (FK)  │
         │              │ start_date      │
         │              │ end_date        │
         │              │ capacity        │
         │              │ status          │
         │              │ created_at      │
         │              └────────┬────────┘
         │                       │
         │              ┌────────▼────────┐
         │              │   lotteries     │
         │              ├─────────────────┤
         │              │ id              │
         │              │ period_id (FK)  │
         │              │ title           │
         │              │ registration_   │
         │              │   start_date    │
         │              │ registration_   │
         │              │   end_date      │
         │              │ draw_date       │
         │              │ status          │
         │              │ algorithm       │
         │              │ created_by      │
         │              │ created_at      │
         │              └────────┬────────┘
         │                       │
         │              ┌────────▼────────┐
         └─────────────▶│ lottery_entries │
                        ├─────────────────┤
                        │ id              │
                        │ lottery_id (FK) │
                        │ personnel_id    │
                        │ province_id     │
                        │ family_count    │
                        │ preferred_units │
                        │ priority_score  │
                        │ status          │
                        │ approved_by     │
                        │ rejection_reason│
                        │ created_at      │
                        └─────────────────┘

┌─────────────────┐     ┌─────────────────┐
│  usage_history  │     │     users       │
├─────────────────┤     ├─────────────────┤
│ id              │     │ id              │
│ personnel_id    │     │ name            │
│ center_id       │     │ email           │
│ reservation_id  │     │ password        │
│ check_in_date   │     │ role            │
│ check_out_date  │     │ province_id     │
│ tariff_type     │     │ created_at      │
│ created_at      │     └─────────────────┘
└─────────────────┘
```

### 7.2 Migrations

```php
// create_provinces_table.php
Schema::create('provinces', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code', 10)->unique();
    $table->integer('personnel_count')->default(0);
    $table->boolean('is_tehran')->default(false);
    $table->timestamps();
});

// create_centers_table.php
Schema::create('centers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('city');
    $table->enum('type', ['religious', 'beach', 'mountain']);
    $table->integer('bed_count')->default(0);
    $table->integer('unit_count')->default(0);
    $table->date('active_from');
    $table->date('active_to');
    $table->integer('stay_duration'); // شب
    $table->timestamps();
});

// create_units_table.php
Schema::create('units', function (Blueprint $table) {
    $table->id();
    $table->foreignId('center_id')->constrained()->onDelete('cascade');
    $table->string('number');
    $table->string('name')->nullable();
    $table->integer('bed_count');
    $table->enum('type', ['room', 'suite', 'villa', 'apartment']);
    $table->integer('floor')->nullable();
    $table->json('amenities')->nullable();
    $table->boolean('is_management')->default(false);
    $table->enum('status', ['available', 'maintenance', 'blocked'])->default('available');
    $table->timestamps();
});

// create_personnels_table.php
Schema::create('personnels', function (Blueprint $table) {
    $table->id();
    $table->string('employee_code')->unique();
    $table->string('national_code', 10)->unique();
    $table->string('full_name');
    $table->foreignId('province_id')->constrained();
    $table->string('bale_user_id')->nullable();
    $table->string('phone', 11)->nullable();
    $table->boolean('is_isargar')->default(false);
    $table->enum('isargar_type', ['veteran', 'freed', 'martyr_family', 'veteran_70', 'veteran_mental'])->nullable();
    $table->integer('service_years')->default(0);
    $table->integer('family_count')->default(1);
    $table->timestamps();
});

// create_lotteries_table.php
Schema::create('lotteries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('period_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->date('registration_start_date');
    $table->date('registration_end_date');
    $table->date('draw_date');
    $table->enum('status', ['draft', 'open', 'closed', 'drawn', 'completed'])->default('draft');
    $table->string('algorithm')->default('weighted_random');
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});

// create_lottery_entries_table.php
Schema::create('lottery_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lottery_id')->constrained()->onDelete('cascade');
    $table->foreignId('personnel_id')->constrained()->onDelete('cascade');
    $table->foreignId('province_id')->constrained();
    $table->integer('family_count');
    $table->json('preferred_units')->nullable();
    $table->decimal('priority_score', 10, 2)->default(0);
    $table->enum('status', ['pending', 'won', 'lost', 'waitlist', 'approved', 'rejected', 'cancelled'])->default('pending');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->text('rejection_reason')->nullable();
    $table->timestamps();

    $table->unique(['lottery_id', 'personnel_id']);
});
```

---

## 8. API Endpoints

### 8.1 Authentication

```
POST   /api/auth/login              # ورود با کد پرسنلی + کد ملی
POST   /api/auth/bale-verify        # احراز هویت از بله
POST   /api/auth/logout             # خروج
GET    /api/auth/profile            # پروفایل کاربر
```

### 8.2 Centers & Periods

```
GET    /api/centers                          # لیست مراکز
GET    /api/centers/{id}                     # جزئیات مرکز
GET    /api/centers/{id}/units               # واحدهای مرکز
GET    /api/centers/{id}/periods             # دوره‌های مرکز
GET    /api/periods/{id}                     # جزئیات دوره
GET    /api/periods/{id}/availability        # ظرفیت خالی
```

### 8.3 Lottery (User)

```
GET    /api/lotteries                        # قرعه‌کشی‌های فعال
GET    /api/lotteries/{id}                   # جزئیات قرعه‌کشی
POST   /api/lotteries/{id}/enter             # شرکت در قرعه‌کشی
DELETE /api/lotteries/{id}/withdraw          # انصراف
GET    /api/lotteries/{id}/my-status         # وضعیت من
GET    /api/lotteries/{id}/results           # نتایج
```

### 8.4 User

```
GET    /api/user/history                     # سابقه استفاده
GET    /api/user/eligibility                 # واجد شرایط بودن
GET    /api/user/score                       # امتیاز فعلی
GET    /api/user/reservations                # رزروهای من
```

### 8.5 Admin - Centers

```
GET    /api/admin/centers                    # لیست مراکز
POST   /api/admin/centers                    # ایجاد مرکز
PUT    /api/admin/centers/{id}               # ویرایش مرکز
DELETE /api/admin/centers/{id}               # حذف مرکز
POST   /api/admin/centers/{id}/units         # افزودن واحد
PUT    /api/admin/units/{id}                 # ویرایش واحد
```

### 8.6 Admin - Lottery

```
GET    /api/admin/lotteries                  # لیست قرعه‌کشی‌ها
POST   /api/admin/lotteries                  # ایجاد قرعه‌کشی
PUT    /api/admin/lotteries/{id}             # ویرایش
POST   /api/admin/lotteries/{id}/open        # باز کردن ثبت‌نام
POST   /api/admin/lotteries/{id}/close       # بستن ثبت‌نام
POST   /api/admin/lotteries/{id}/draw        # اجرای قرعه‌کشی
GET    /api/admin/lotteries/{id}/entries     # لیست شرکت‌کنندگان
GET    /api/admin/lotteries/{id}/winners     # لیست برندگان
```

### 8.7 Provincial Admin

```
GET    /api/provincial/quota                 # سهمیه استان
GET    /api/provincial/entries               # درخواست‌های استان
POST   /api/provincial/entries/{id}/approve  # تأیید
POST   /api/provincial/entries/{id}/reject   # رد
GET    /api/provincial/reports               # گزارش استان
```

### 8.8 Reports

```
GET    /api/admin/reports/occupancy          # نرخ اشغال
GET    /api/admin/reports/province           # به تفکیک استان
GET    /api/admin/reports/fairness           # تحلیل عدالت
GET    /api/admin/reports/financial          # مالی
GET    /api/admin/reports/export             # خروجی Excel
```

---

## 9. مینی‌اپ بله

### 9.1 ساختار صفحات

```
مینی‌اپ بله
├── 🏠 صفحه اصلی
│   ├── مراکز رفاهی
│   ├── قرعه‌کشی‌های فعال
│   └── آخرین اخبار
│
├── 🎰 قرعه‌کشی
│   ├── لیست قرعه‌کشی‌های باز
│   ├── شرکت در قرعه‌کشی
│   └── نتایج قرعه‌کشی
│
├── 📋 رزروهای من
│   ├── رزروهای فعال
│   ├── سابقه رزرو
│   └── معرفی‌نامه
│
├── 👤 پروفایل
│   ├── اطلاعات من
│   ├── امتیاز من
│   └── خانواده بانکی
│
└── ℹ️ راهنما
    ├── قوانین
    └── تماس با ما
```

### 9.2 User Journey

```
1️⃣ ورود به مینی‌اپ از طریق بله
         ↓
2️⃣ احراز هویت (کد پرسنلی + کد ملی یا شماره تلفن بله)
         ↓
3️⃣ داشبورد: مشاهده سابقه، امتیاز، قرعه‌کشی‌های فعال
         ↓
4️⃣ انتخاب قرعه‌کشی و ثبت درخواست
   ├── انتخاب مرکز
   ├── انتخاب بازه زمانی
   ├── تعداد همراهان
   └── اولویت واحد (اختیاری)
         ↓
5️⃣ انتظار برای قرعه‌کشی
         ↓
6️⃣ دریافت نتیجه (اعلان بله)
   ├── ✅ برنده → دریافت معرفی‌نامه
   └── ❌ عدم برد → قرار گرفتن در لیست انتظار
```

### 9.3 کد نمونه Bale SDK

```javascript
// main.js - Vue 3 + Bale Mini App SDK

import { createApp } from 'vue'
import App from './App.vue'

// دریافت اطلاعات کاربر از بله
const initData = window.Bale?.WebApp?.initData
const user = window.Bale?.WebApp?.initDataUnsafe?.user

// ارسال به سرور برای احراز هویت
async function authenticate() {
  const response = await fetch('/api/auth/bale-verify', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      initData: initData,
      baleUserId: user?.id
    })
  })
  return response.json()
}

// درخواست شماره تلفن
function requestPhone() {
  window.Bale?.WebApp?.requestContact((shared, phone) => {
    if (shared) {
      linkPhone(phone)
    }
  })
}

// تنظیم تم
function setupTheme() {
  const theme = window.Bale?.WebApp?.themeParams
  document.documentElement.style.setProperty('--bg-color', theme?.bg_color || '#fff')
  document.documentElement.style.setProperty('--text-color', theme?.text_color || '#000')
}

createApp(App).mount('#app')
```

---

## 10. فازبندی پروژه

### فاز 0: آماده‌سازی زیرساخت

| تسک | وضعیت |
|-----|-------|
| ایجاد دیتابیس PostgreSQL | ⬜ |
| تنظیم Redis | ⬜ |
| ایجاد پروژه Laravel | ⬜ |
| تنظیم Docker Compose | ⬜ |
| تست اتصال به بله | ⬜ |

### فاز 1: MVP (مدت: 4 هفته)

| تسک | وضعیت |
|-----|-------|
| Migrations & Models | ⬜ |
| Seeders (3 مرکز + استان‌ها) | ⬜ |
| CRUD مراکز و واحدها | ⬜ |
| CRUD دوره‌ها و فصول | ⬜ |
| ثبت‌نام و احراز هویت | ⬜ |
| قرعه‌کشی ساده | ⬜ |
| مینی‌اپ بله (ساده) | ⬜ |

### فاز 2: قرعه‌کشی پیشرفته (مدت: 2 هفته)

| تسک | وضعیت |
|-----|-------|
| الگوریتم امتیازدهی | ⬜ |
| توزیع سهمیه استانی | ⬜ |
| تأیید مدیریت استانی | ⬜ |
| جایگزینی خودکار | ⬜ |
| اعلان نتایج (Bale Bot) | ⬜ |

### فاز 3: گزارش‌گیری (مدت: 2 هفته)

| تسک | وضعیت |
|-----|-------|
| داشبورد مدیریتی | ⬜ |
| گزارش اشغال | ⬜ |
| گزارش استانی | ⬜ |
| تحلیل عدالت | ⬜ |
| خروجی Excel | ⬜ |

### فاز 4: تکمیلی (مدت: 2 هفته)

| تسک | وضعیت |
|-----|-------|
| پنل استانی کامل | ⬜ |
| سیستم تخفیف | ⬜ |
| صدور معرفی‌نامه | ⬜ |
| اعلان پیامکی | ⬜ |
| بهینه‌سازی | ⬜ |

---

## 11. زیرساخت سرور

### 11.1 مشخصات سرور موجود

| شاخص | مقدار |
|------|-------|
| **IP** | 37.152.174.87 |
| **OS** | Ubuntu 22.04.5 LTS |
| **CPU** | 8 Core (Intel Broadwell) |
| **RAM** | 32 GB |
| **Disk** | 88 GB (26% used) |
| **Docker** | ✅ نصب شده |
| **PostgreSQL** | ✅ موجود (miniapbale_postgres) |
| **Nginx** | ✅ موجود |

### 11.2 سرویس‌های موجود

| سرویس | پورت | وضعیت |
|-------|------|--------|
| Nginx | 80/443 | ✅ |
| PostgreSQL | 5432 | ✅ |
| Hotel | 8082 | ✅ |
| MiniApBale | 8081 | ✅ |
| N8N | 5678 | ✅ |
| Portainer | 9443 | ✅ |

### 11.3 پورت جدید برای Welfare System

```
welfare-app:    8083  (Laravel)
welfare-redis:  6379  (Redis - internal)
```

### 11.4 Nginx Config

```nginx
# /etc/nginx/sites-available/welfare.conf

server {
    listen 80;
    server_name welfare.darmanjoo.ir;

    location / {
        proxy_pass http://localhost:8083;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # For Bale Mini App
        add_header Content-Security-Policy "frame-ancestors 'self' https://*.bale.ai" always;
    }
}
```

---

## پیوست‌ها

### پیوست 1: لیست استان‌ها و سهمیه

| # | استان | کد | سهمیه تقریبی |
|---|-------|-----|-------------|
| 1 | آذربایجان شرقی | AZS | 3% |
| 2 | آذربایجان غربی | AZG | 2% |
| 3 | اردبیل | ARD | 1% |
| ... | ... | ... | ... |
| 31 | یزد | YZD | 1% |
| 32-37 | تهران (6 اداره) | TH1-TH6 | 15% |

### پیوست 2: نقشی‌ها و دسترسی‌ها

| نقش | دسترسی |
|-----|--------|
| **Super Admin** | همه |
| **Admin** | مدیریت مراکز، قرعه‌کشی، گزارش‌ها |
| **Provincial Admin** | تأیید/رد، گزارش استان |
| **Operator** | مشاهده، ورود اطلاعات |
| **User** | ثبت درخواست، مشاهده نتایج |

---

**تاریخ تنظیم:** دی ۱۴۰۴
**نسخه:** 3.0
**وضعیت:** آماده اجرا ✅
