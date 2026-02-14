# 🎯 راهنمای سیستم مدیریت مهمانان (Guest Management System)

## 📋 خلاصه تغییرات

### ✅ فایل‌های ایجاد شده

#### 1. Database Migrations
- `database/migrations/2026_02_14_000001_create_guests_table.php`
  - جدول `guests` (مهمانان یکتا)
  - جدول `personnel_guests` (many-to-many)

- `database/migrations/2026_02_14_000002_add_selected_guest_ids_to_lottery_entries.php`
  - فیلد `selected_guest_ids` به جدول `lottery_entries`

#### 2. Models
- `app/Models/Guest.php` - مدل مهمان
- تغییرات در `app/Models/Personnel.php`:
  - اضافه شدن `BelongsToMany` import
  - relation `guests()`
  - متدهای `getBankAffiliatedGuestsCount()` و `getNonBankAffiliatedGuestsCount()`

- تغییرات در `app/Models/LotteryEntry.php`:
  - فیلد `selected_guest_ids` به fillable و casts
  - متد `selectedGuests()` برای دریافت مهمانان انتخاب شده
  - متد `getTotalPersonsCount()` برای محاسبه کل افراد

#### 3. Controller
- `app/Http/Controllers/GuestController.php` - مدیریت CRUD مهمانان

#### 4. Routes
- تغییرات در `routes/web.php`:
  - اضافه شدن `GuestController` import
  - Route group برای `personnel/{personnel}/guests`

#### 5. Views
- `resources/views/personnel/partials/_guests_tab.blade.php` - تب مهمانان

---

## 🚀 نحوه استفاده

### مرحله 1: اجرای Migrations

```bash
# با Docker
docker-compose exec app php artisan migrate

# یا بدون Docker (در صورت نصب محلی PHP)
php artisan migrate
```

### مرحله 2: استفاده در صفحه Personnel Show

در فایل `resources/views/personnel/show.blade.php`، تب مهمانان را اضافه کنید:

```blade
{{-- در قسمتی که می‌خواهید تب مهمانان نمایش داده شود --}}
@include('personnel.partials._guests_tab')
```

### مرحله 3: تست سیستم

1. به صفحه نمایش یک پرسنل بروید: `/personnel/{id}`
2. روی دکمه "افزودن مهمان" کلیک کنید
3. اطلاعات مهمان را وارد کنید
4. مهمان به لیست اضافه می‌شود

---

## 🔄 فلوی کار (Workflow)

### 1. افزودن مهمان به لیست پرسنل
```
صفحه پرسنل → تب مهمانان → افزودن مهمان
→ وارد کردن کد ملی، نام، نسبت، ...
→ ذخیره
→ سیستم چک می‌کند:
   - اگر کد ملی قبلاً وجود داشته باشد → مهمان موجود به لیست متصل می‌شود
   - اگر جدید باشد → مهمان جدید ساخته و به لیست متصل می‌شود
```

### 2. ثبت‌نام برای قرعه‌کشی (آینده)
```
پرسنل ثبت‌نام می‌کند
→ لیست مهمانان خود را می‌بیند (checkboxها)
→ انتخاب می‌کند کدام مهمانان در این سفر همراهند
→ IDs انتخاب شده در lottery_entry.selected_guest_ids ذخیره می‌شود
```

### 3. در رزرو (آینده)
```
پرسنل برنده می‌شود
→ مهمانان انتخاب شده از lottery_entry خوانده می‌شوند
→ در reservation.accompanying_guests ذخیره می‌شوند
→ هنگام check-in تأیید می‌شوند
```

---

## 📊 دسته‌بندی مهمانان

### ✅ خانواده بانکی (Bank Affiliated) - تعرفه کمتر
1. همسر
2. فرزند
3. پدر
4. مادر
5. پدر همسر
6. مادر همسر

### ⚠️ متفرقه (Miscellaneous) - تعرفه بیشتر
1. دوست
2. فامیل
3. سایر

تشخیص نوع:
```php
$guest->isBankAffiliated(); // true/false
$personnel->getBankAffiliatedGuestsCount(); // تعداد مهمانان بانکی
$personnel->getNonBankAffiliatedGuestsCount(); // تعداد مهمانان متفرقه
```

---

## 🗄️ ساختار دیتابیس

### جدول `guests`
```sql
- id
- national_code (UNIQUE)
- full_name
- relation
- birth_date
- gender
- phone
- notes
- created_at, updated_at
```

### جدول `personnel_guests` (pivot)
```sql
- id
- personnel_id (FK → personnel)
- guest_id (FK → guests)
- notes (یادداشت برای این رابطه)
- created_at, updated_at
- UNIQUE(personnel_id, guest_id)
```

### تغییر در `lottery_entries`
```sql
+ selected_guest_ids (JSON) - آرایه از IDs: [1,2,3]
```

---

## 🔌 API Endpoints

### لیست مهمانان یک پرسنل
```http
GET /personnel/{personnel}/guests
Response: {
  "guests": [
    {
      "id": 1,
      "national_code": "1234567890",
      "full_name": "نام مهمان",
      "relation": "همسر",
      "is_bank_affiliated": true,
      "badge_class": "success",
      "badge_text": "بانکی",
      ...
    }
  ]
}
```

### افزودن مهمان
```http
POST /personnel/{personnel}/guests
Body: {
  "national_code": "1234567890",
  "full_name": "نام مهمان",
  "relation": "همسر",
  "birth_date": "1370/01/01",
  "gender": "male",
  "phone": "09123456789",
  "notes": "یادداشت"
}
```

### حذف مهمان از لیست
```http
DELETE /personnel/{personnel}/guests/{guest}
```

---

## 📝 کارهای باقی‌مانده

### Phase 1 (جاری - نیاز به تکمیل)
- [x] ایجاد migrations
- [x] ایجاد Models
- [x] ایجاد GuestController
- [x] اضافه کردن routes
- [x] ساخت view اولیه برای تب مهمانان
- [ ] اضافه کردن include تب مهمانان به `personnel/show.blade.php`
- [ ] تست کامل CRUD عملیات
- [ ] اصلاح فرم‌های ثبت‌نام بات (اگر نیاز باشد)

### Phase 2 (آینده)
- [ ] UI برای انتخاب مهمانان هنگام ثبت‌نام در قرعه‌کشی
  - صفحه lottery entry create/edit
  - نمایش checkbox list از مهمانان پرسنل
  - ذخیره selected_guest_ids
- [ ] نمایش مهمانان انتخاب شده در صفحه lottery entry show
- [ ] محاسبه تعداد کل افراد (پرسنل + مهمانان) برای unit assignment

### Phase 3 (آینده)
- [ ] یکپارچه‌سازی با Reservation
  - کپی selected_guest_ids به reservation.accompanying_guests
  - نمایش لیست مهمانان در voucher
  - check-in/check-out با لیست مهمانان

### Phase 4 (آینده - Beneficiaries)
- [ ] سیستم وظیفه‌بگیران (مطابق PERSONNEL_GUESTS_SPEC.md)

---

## 🐛 نکات مهم

### یکتا بودن مهمان
- مهمانان بر اساس `national_code` یکتا هستند
- یک مهمان (مثلاً مادر) می‌تواند در لیست چند پرسنل باشد (مثلاً دو برادر)
- هر پرسنل نمی‌تواند یک مهمان را دوبار اضافه کند

### لیست قابل ویرایش
- پرسنل می‌تواند مهمانان را اضافه/حذف کند
- هر سفر می‌تواند مهمانان متفاوتی داشته باشد
- مهمانان فراموش شده می‌توانند بعداً اضافه شوند

### اشتراک‌گذاری مهمان
- یک مهمان می‌تواند با چند پرسنل سفر کند (در زمان‌های مختلف)
- هر reservation ثبت می‌کند چه مهمانانی با کدام پرسنل سفر کردند

---

## 🎨 نمونه کد استفاده

### دریافت مهمانان یک پرسنل
```php
$personnel = Personnel::with('guests')->find(1);

foreach ($personnel->guests as $guest) {
    echo $guest->full_name . ' - ' . $guest->relation;
    echo ' (' . ($guest->isBankAffiliated() ? 'بانکی' : 'متفرقه') . ')';
}
```

### افزودن مهمان
```php
// مهمان جدید یا موجود
$guest = Guest::createOrUpdate([
    'national_code' => '1234567890',
    'full_name' => 'نام مهمان',
    'relation' => 'همسر',
]);

// اتصال به پرسنل
$personnel->guests()->attach($guest->id, [
    'notes' => 'یادداشت اختیاری'
]);
```

### حذف مهمان از لیست
```php
$personnel->guests()->detach($guestId);
```

### دریافت مهمانان انتخاب شده در یک lottery entry
```php
$lotteryEntry = LotteryEntry::find(1);
$selectedGuests = $lotteryEntry->selectedGuests();

foreach ($selectedGuests as $guest) {
    echo $guest->full_name;
}

$totalPersons = $lotteryEntry->getTotalPersonsCount(); // پرسنل + مهمانان
```

---

## 🔍 تست و دیباگ

### بررسی migrations
```bash
docker-compose exec app php artisan migrate:status
```

### بررسی routes
```bash
docker-compose exec app php artisan route:list --name=personnel.guests
```

### Test در Tinker
```bash
docker-compose exec app php artisan tinker

# افزودن مهمان
>>> $personnel = Personnel::find(1);
>>> $guest = Guest::create(['national_code' => '1234567890', 'full_name' => 'Test', 'relation' => 'همسر']);
>>> $personnel->guests()->attach($guest->id);
>>> $personnel->guests;

# تست relation
>>> $guest->isBankAffiliated();
```

---

**تاریخ ایجاد:** 1404/11/26
**وضعیت:** در حال پیاده‌سازی Phase 1
