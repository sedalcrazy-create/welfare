# Family Members System Specification
## سیستم ثبت اطلاعات همراهان

**Date**: 2026-02-11
**Status**: 🔄 In Implementation
**Version**: 1.3.0-family-members
**Previous Version**: 1.2.0-phase1-revised

---

## 📋 Overview

این specification سیستم ثبت جزئیات اطلاعات همراهان (اعضای خانواده) را پیاده‌سازی می‌کند.

### 🎯 اهداف اصلی:

1. **ثبت اطلاعات کامل همراهان**: هر پرسنل باید اطلاعات جزئی همراهان خود را وارد کند
2. **کد پرسنلی اجباری**: سرپرست اصلی باید کد پرسنلی معتبر داشته باشد
3. **مشخص کردن نسبت**: نسبت هر همراه با سرپرست مشخص شود (همسر، فرزند، والدین، ...)
4. **یکپارچگی در پنل و بات**: هم در پنل ادمین و هم در بات بله اطلاعات یکسان وارد شود

---

## 🔄 Changes from Previous Version (1.2.0)

| موضوع | نسخه قبلی | نسخه جدید |
|-------|-----------|----------|
| **اطلاعات همراهان** | فقط `family_count` (تعداد) | اطلاعات کامل هر همراه |
| **کد پرسنلی** | nullable | اجباری (required) |
| **نسبت همراهان** | مشخص نبود | همسر، فرزند، والدین، ... |
| **اطلاعات شناسایی** | فقط تعداد | نام، کد ملی، تاریخ تولد |

---

## 🗄️ Database Schema Changes

### 1. Modified Table: `personnel`

```sql
ALTER TABLE personnel
-- اضافه کردن فیلد JSON برای اطلاعات همراهان
ADD COLUMN family_members JSON COMMENT 'اطلاعات جزئی همراهان (نام، نسبت، کد ملی، تاریخ تولد)',

-- employee_code باید اجباری باشد (در migration جدید این تغییر اعمال می‌شود)
-- برای رکوردهای موجود که employee_code ندارند، یک مقدار پیش‌فرض تولید می‌شود
MODIFY COLUMN employee_code VARCHAR(20) NOT NULL;
```

**ساختار JSON برای `family_members`:**
```json
[
  {
    "full_name": "فاطمه محمدی",
    "relation": "همسر",
    "national_code": "0987654321",
    "birth_date": "1370/01/01",
    "gender": "female",
    "age": 34
  },
  {
    "full_name": "محمد احمدی",
    "relation": "فرزند",
    "national_code": "1122334455",
    "birth_date": "1395/05/10",
    "gender": "male",
    "age": 8
  }
]
```

**مقادیر مجاز برای `relation`:**
- `همسر` (spouse)
- `فرزند` (child)
- `پدر` (father)
- `مادر` (mother)
- `سایر` (other)

---

## 🔧 Implementation Details

### 1. Personnel Model Changes

```php
// app/Models/Personnel.php

protected $fillable = [
    // ... existing fields
    'family_members',
];

protected $casts = [
    // ... existing casts
    'family_members' => 'array',
];

// Constants for family relations
public const RELATION_SPOUSE = 'همسر';
public const RELATION_CHILD = 'فرزند';
public const RELATION_FATHER = 'پدر';
public const RELATION_MOTHER = 'مادر';
public const RELATION_OTHER = 'سایر';

// Helper methods
public function getFamilyMembersCount(): int
{
    return $this->family_members ? count($this->family_members) : 0;
}

public function getTotalPersonsCount(): int
{
    // سرپرست + همراهان
    return 1 + $this->getFamilyMembersCount();
}

public function hasFamilyMembers(): bool
{
    return !empty($this->family_members);
}

// Update family_count automatically
protected static function boot()
{
    parent::boot();

    static::saving(function ($personnel) {
        // محاسبه خودکار family_count از روی family_members
        if (isset($personnel->family_members)) {
            $personnel->family_count = count($personnel->family_members) + 1; // +1 برای خود سرپرست
        }
    });
}
```

---

### 2. Validation Rules

#### Web Panel (`PersonnelRequestController`)

```php
public function store(Request $request)
{
    $validated = $request->validate([
        // سرپرست اصلی
        'employee_code' => 'required|string|max:20',
        'full_name' => 'required|string|max:255',
        'national_code' => 'required|string|size:10|unique:personnel,national_code',
        'phone' => 'required|string|max:20',
        'preferred_center_id' => 'required|exists:centers,id',
        'province_id' => 'nullable|exists:provinces,id',
        'notes' => 'nullable|string|max:1000',

        // همراهان
        'family_members' => 'nullable|array|max:10',
        'family_members.*.full_name' => 'required|string|max:255',
        'family_members.*.relation' => [
            'required',
            'string',
            Rule::in(['همسر', 'فرزند', 'پدر', 'مادر', 'سایر'])
        ],
        'family_members.*.national_code' => 'required|string|size:10',
        'family_members.*.birth_date' => 'nullable|string|max:10', // تاریخ شمسی
        'family_members.*.gender' => 'required|in:male,female',
    ]);

    // ...
}
```

#### Bale Bot API (`Api\PersonnelRequestController`)

```php
public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        // سرپرست اصلی
        'employee_code' => 'required|string|max:20',
        'full_name' => 'required|string|max:255',
        'national_code' => 'required|string|size:10|unique:personnel,national_code',
        'phone' => 'required|string|max:20',
        'preferred_center_id' => 'required|exists:centers,id',
        'bale_user_id' => 'nullable|string|unique:personnel,bale_user_id',

        // همراهان
        'family_members' => 'nullable|array|max:10',
        'family_members.*.full_name' => 'required|string|max:255',
        'family_members.*.relation' => [
            'required',
            'string',
            Rule::in(['همسر', 'فرزند', 'پدر', 'مادر', 'سایر'])
        ],
        'family_members.*.national_code' => 'required|string|size:10',
        'family_members.*.birth_date' => 'nullable|string|max:10',
        'family_members.*.gender' => 'required|in:male,female',
    ], [
        'employee_code.required' => 'کد پرسنلی الزامی است',
        'national_code.unique' => 'این کد ملی قبلاً ثبت شده است',
        'family_members.*.national_code.size' => 'کد ملی باید 10 رقم باشد',
        'family_members.*.relation.in' => 'نسبت وارد شده معتبر نیست',
    ]);

    // ...
}
```

---

### 3. API Request/Response Examples

#### Request (Web Panel)
```json
POST /personnel-requests

{
  "employee_code": "12345",
  "full_name": "علی احمدی",
  "national_code": "1234567890",
  "phone": "09123456789",
  "preferred_center_id": 1,
  "province_id": 8,

  "family_members": [
    {
      "full_name": "فاطمه محمدی",
      "relation": "همسر",
      "national_code": "0987654321",
      "birth_date": "1370/01/01",
      "gender": "female"
    },
    {
      "full_name": "محمد احمدی",
      "relation": "فرزند",
      "national_code": "1122334455",
      "birth_date": "1395/05/10",
      "gender": "male"
    }
  ]
}
```

#### Request (Bale Bot)
```json
POST /api/bale/register

{
  "employee_code": "12345",
  "full_name": "علی احمدی",
  "national_code": "1234567890",
  "phone": "09123456789",
  "preferred_center_id": 1,
  "bale_user_id": "123456789",

  "family_members": [
    {
      "full_name": "فاطمه محمدی",
      "relation": "همسر",
      "national_code": "0987654321",
      "birth_date": "1370/01/01",
      "gender": "female"
    }
  ]
}
```

#### Response
```json
{
  "success": true,
  "message": "درخواست با موفقیت ثبت شد",
  "data": {
    "tracking_code": "WLF-0411-1234",
    "employee_code": "12345",
    "full_name": "علی احمدی",
    "total_persons": 3,
    "family_members_count": 2,
    "preferred_center": "زائرسرای مشهد",
    "status": "در انتظار بررسی"
  }
}
```

---

### 4. Frontend Changes

#### Personnel Request Create Form

**قسمت 1: اطلاعات سرپرست**
```html
<div class="card">
    <div class="card-header">اطلاعات سرپرست اصلی</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <label>کد پرسنلی *</label>
                <input type="text" name="employee_code" required>
            </div>
            <div class="col-md-6">
                <label>نام و نام خانوادگی *</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="col-md-6">
                <label>کد ملی *</label>
                <input type="text" name="national_code" required>
            </div>
            <div class="col-md-6">
                <label>تلفن همراه *</label>
                <input type="text" name="phone" required>
            </div>
        </div>
    </div>
</div>
```

**قسمت 2: افزودن همراهان**
```html
<div class="card mt-3">
    <div class="card-header">
        همراهان
        <button type="button" id="add-family-member" class="btn btn-sm btn-primary float-end">
            افزودن همراه
        </button>
    </div>
    <div class="card-body">
        <div id="family-members-container">
            <!-- JavaScript will populate this -->
        </div>
    </div>
</div>
```

**JavaScript for dynamic family members:**
```javascript
let memberIndex = 0;

document.getElementById('add-family-member').addEventListener('click', function() {
    const container = document.getElementById('family-members-container');
    const memberHtml = `
        <div class="family-member-row border p-3 mb-3" data-index="${memberIndex}">
            <div class="row">
                <div class="col-md-6">
                    <label>نام و نام خانوادگی</label>
                    <input type="text" name="family_members[${memberIndex}][full_name]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>نسبت</label>
                    <select name="family_members[${memberIndex}][relation]" class="form-control" required>
                        <option value="">انتخاب کنید</option>
                        <option value="همسر">همسر</option>
                        <option value="فرزند">فرزند</option>
                        <option value="پدر">پدر</option>
                        <option value="مادر">مادر</option>
                        <option value="سایر">سایر</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>کد ملی</label>
                    <input type="text" name="family_members[${memberIndex}][national_code]" class="form-control" maxlength="10" required>
                </div>
                <div class="col-md-4">
                    <label>تاریخ تولد</label>
                    <input type="text" name="family_members[${memberIndex}][birth_date]" class="form-control persian-date" placeholder="1370/01/01">
                </div>
                <div class="col-md-3">
                    <label>جنسیت</label>
                    <select name="family_members[${memberIndex}][gender]" class="form-control" required>
                        <option value="">انتخاب کنید</option>
                        <option value="male">مرد</option>
                        <option value="female">زن</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-member w-100">حذف</button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', memberHtml);
    memberIndex++;
});

// Remove family member
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-member')) {
        e.target.closest('.family-member-row').remove();
    }
});
```

---

### 5. View Display Changes

**Personnel Request Show Page:**
```blade
{{-- اطلاعات سرپرست --}}
<div class="card">
    <div class="card-header">اطلاعات سرپرست</div>
    <div class="card-body">
        <table class="table">
            <tr>
                <th>کد پرسنلی:</th>
                <td>{{ $personnelRequest->employee_code }}</td>
            </tr>
            <tr>
                <th>نام و نام خانوادگی:</th>
                <td>{{ $personnelRequest->full_name }}</td>
            </tr>
            <tr>
                <th>کد ملی:</th>
                <td>{{ $personnelRequest->national_code }}</td>
            </tr>
            <tr>
                <th>تلفن:</th>
                <td>{{ $personnelRequest->phone }}</td>
            </tr>
        </table>
    </div>
</div>

{{-- لیست همراهان --}}
@if($personnelRequest->hasFamilyMembers())
<div class="card mt-3">
    <div class="card-header">
        همراهان ({{ $personnelRequest->getFamilyMembersCount() }} نفر)
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام و نام خانوادگی</th>
                    <th>نسبت</th>
                    <th>کد ملی</th>
                    <th>تاریخ تولد</th>
                    <th>جنسیت</th>
                </tr>
            </thead>
            <tbody>
                @foreach($personnelRequest->family_members as $index => $member)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $member['full_name'] }}</td>
                    <td>{{ $member['relation'] }}</td>
                    <td>{{ $member['national_code'] }}</td>
                    <td>{{ $member['birth_date'] ?? '-' }}</td>
                    <td>{{ $member['gender'] === 'male' ? 'مرد' : 'زن' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p class="text-muted mt-2">
            جمع کل افراد: {{ $personnelRequest->getTotalPersonsCount() }} نفر
        </p>
    </div>
</div>
@endif
```

---

## 📊 Migration Strategy

### Migration File: `2026_02_11_add_family_members_to_personnel.php`

```php
public function up(): void
{
    Schema::table('personnel', function (Blueprint $table) {
        // اضافه کردن فیلد JSON برای همراهان
        $table->json('family_members')->nullable()->after('family_count');
    });

    // برای رکوردهای موجود که employee_code ندارند، یک مقدار پیش‌فرض تولید می‌کنیم
    DB::statement('UPDATE personnel SET employee_code = CONCAT("TEMP-", id) WHERE employee_code IS NULL');

    // حالا employee_code را اجباری می‌کنیم
    Schema::table('personnel', function (Blueprint $table) {
        $table->string('employee_code', 20)->nullable(false)->change();
    });
}

public function down(): void
{
    Schema::table('personnel', function (Blueprint $table) {
        $table->dropColumn('family_members');
        $table->string('employee_code', 20)->nullable()->change();
    });
}
```

---

## ✅ Acceptance Criteria

- [ ] فیلد `family_members` به جدول personnel اضافه شده
- [ ] فیلد `employee_code` اجباری شده
- [ ] Personnel Model شامل helper methods برای کار با family_members است
- [ ] Validation rules در Web و API درست کار می‌کند
- [ ] فرم ثبت پرسنل امکان افزودن/حذف همراه را دارد
- [ ] صفحه نمایش پرسنل لیست همراهان را نشان می‌دهد
- [ ] API بات بله اطلاعات همراهان را دریافت و ذخیره می‌کند
- [ ] تعداد کل افراد (سرپرست + همراهان) به درستی محاسبه می‌شود

---

## 🔐 Security Considerations

1. **Validation**: کد ملی همراهان باید یونیک نباشد (ممکن است یک نفر در چند درخواست باشد)
2. **Data Privacy**: اطلاعات همراهان محرمانه است و فقط به ادمین و صاحب درخواست نمایش داده شود
3. **Input Sanitization**: تمام ورودی‌ها قبل از ذخیره sanitize شوند
4. **Array Limit**: حداکثر 10 همراه برای جلوگیری از abuse

---

## 📝 Notes

- فیلد `family_count` به صورت خودکار از روی `family_members` محاسبه می‌شود
- تاریخ تولد به صورت اختیاری است (برای کودکان خردسال ممکن است دقیق نباشد)
- نسبت "سایر" برای موارد خاص (مثل خواهر، برادر، ...) استفاده می‌شود

---

**End of Specification**
