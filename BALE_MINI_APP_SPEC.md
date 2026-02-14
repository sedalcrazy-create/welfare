# مینی‌اپ بله - سامانه رفاهی بانک ملی
## Bale Mini App Specification v1.0

---

## 📱 **نمای کلی (Overview)**

مینی‌اپ بله برای سامانه رفاهی بانک ملی، یک Progressive Web App (PWA) است که:
- **Mobile-First Design**: طراحی اختصاصی برای گوشی موبایل
- **Seamless Authentication**: ورود خودکار با Bale OAuth (بدون username/password)
- **Full-Featured**: دسترسی کامل به تمام قابلیت‌های سیستم معرفی‌نامه
- **Responsive**: کار در همه سایزهای صفحه نمایش

---

## 🎯 **User Flow (فلوی کاربری)**

### **مرحله 1: ورود به مینی‌اپ**
```
کاربر در بله → باز کردن Bot → کلیک دکمه Mini App
    ↓
Bale Mini App SDK → ارسال initData
    ↓
Backend Laravel → تأیید initData با Bale API
    ↓
ساخت/بروزرسانی User + ایجاد Sanctum Token
    ↓
ذخیره Token در LocalStorage
    ↓
ورود به Home Screen
```

### **مرحله 2: اولین بار (First Time User)**
```
کاربر جدید → صفحه Welcome
    ↓
فرم تکمیل اطلاعات پرسنل:
  - کد پرسنلی
  - کد ملی
  - نام و نام خانوادگی
  - استان/واحد
  - موبایل
    ↓
ثبت اطلاعات → منتظر تأیید ادمین (pending)
    ↓
نمایش صفحه "در انتظار تأیید"
```

### **مرحله 3: کاربر تأیید شده (Approved User)**
```
Home Screen
    ├── وضعیت سهمیه (برای هر مرکز)
    ├── قرعه‌کشی‌های باز
    ├── معرفی‌نامه‌های فعال
    └── دسترسی سریع
```

### **مرحله 4: فلوی صدور معرفی‌نامه**
```
کلیک "صدور معرفی‌نامه جدید"
    ↓
انتخاب مرکز (Mashhad/Babolsar/Chadegan)
    ↓
انتخاب/مدیریت مهمانان:
  ├── لیست مهمانان ذخیره شده
  ├── اضافه کردن مهمان جدید
  │   ├── کد ملی
  │   ├── نام کامل
  │   ├── نسبت
  │   ├── تاریخ تولد
  │   └── جنسیت
  └── اضافه کردن پرسنل دیگر (جستجو با کد پرسنلی)
    ↓
بررسی سهمیه و قوانین (3 years rule)
    ↓
تأیید و ثبت درخواست
    ↓
منتظر تأیید مدیر استانی (pending)
    ↓
پس از تأیید: صدور معرفی‌نامه
    ↓
دانلود/اشتراک‌گذاری معرفی‌نامه (PDF)
```

---

## 🏗️ **معماری سیستم (Architecture)**

### **Frontend Stack**
```
Vue 3 + Vite
├── Vue Router (SPA navigation)
├── Pinia (State management)
├── Axios (HTTP client)
├── Tailwind CSS (Styling - mobile-first)
├── Bale Mini App SDK (Authentication)
└── PWA Plugin (Offline support)
```

### **Backend Stack**
```
Laravel 11 API
├── Sanctum (Token authentication)
├── Existing Business Logic (کد موجود تغییری نمی‌کند)
├── API Resources (برای سریالایز کردن داده‌ها)
└── Bale Verification Service
```

### **Directory Structure**
```
welfare-V2/
├── public/
│   └── mini-app/              # Built PWA files (production)
├── resources/
│   ├── mini-app/              # Vue.js source code
│   │   ├── src/
│   │   │   ├── assets/
│   │   │   ├── components/
│   │   │   │   ├── common/   # دکمه‌ها، کارت‌ها، اینپوت‌ها
│   │   │   │   ├── guests/   # مدیریت مهمانان
│   │   │   │   ├── letters/  # معرفی‌نامه‌ها
│   │   │   │   └── layout/   # Header, Footer, Navbar
│   │   │   ├── composables/  # Vue composables (useAuth, useGuests, ...)
│   │   │   ├── router/       # Vue Router config
│   │   │   ├── stores/       # Pinia stores
│   │   │   ├── views/        # صفحات اصلی
│   │   │   ├── App.vue
│   │   │   └── main.js
│   │   ├── index.html
│   │   ├── package.json
│   │   └── vite.config.js
├── app/
│   └── Http/
│       └── Controllers/
│           └── Api/
│               └── MiniApp/   # کنترلرهای مخصوص Mini App
├── routes/
│   └── api.php               # API routes for Mini App
└── BALE_MINI_APP_SPEC.md     # این فایل
```

---

## 🎨 **طراحی UI/UX (Mobile-First)**

### **رنگ‌بندی (Color Palette)**
```css
/* Bale Brand Colors */
--primary: #00A6A6;        /* رنگ اصلی بله */
--primary-dark: #008585;   /* تیره‌تر */
--secondary: #FF6B6B;      /* رنگ تأکیدی */
--success: #51CF66;        /* موفقیت */
--warning: #FFD93D;        /* هشدار */
--danger: #FF6B6B;         /* خطر */
--gray-50: #F9FAFB;
--gray-100: #F3F4F6;
--gray-900: #111827;
```

### **Typography**
```css
font-family: 'Vazirmatn', 'Segoe UI', sans-serif;
font-size: 14px (base - mobile)
line-height: 1.5
```

### **صفحات (Views)**

#### **1. Welcome Screen (اولین بار)**
- لوگو + عنوان
- توضیح مختصر
- دکمه "شروع کنید"

#### **2. Registration Form (ثبت‌نام اولیه)**
- فرم چند مرحله‌ای (Multi-step)
- Step 1: اطلاعات پایه (کد پرسنلی، کد ملی، نام)
- Step 2: اطلاعات تماس (موبایل، استان)
- Progress indicator
- دکمه‌های "بعدی" و "قبلی"

#### **3. Pending Approval Screen**
- آیکون ساعت شنی
- متن: "درخواست شما در حال بررسی است"
- دکمه "بروزرسانی وضعیت"

#### **4. Home Screen (صفحه اصلی)**
```
┌──────────────────────────┐
│  Header                  │
│  👤 سلام، [نام کاربر]    │
└──────────────────────────┘
┌──────────────────────────┐
│  Quota Cards (3 مرکز)    │
│  ┌─────┐ ┌─────┐ ┌─────┐│
│  │مشهد│ │بابلسر│ │چادگان││
│  └─────┘ └─────┘ └─────┘│
└──────────────────────────┘
┌──────────────────────────┐
│  Quick Actions           │
│  [صدور معرفی‌نامه]       │
│  [مهمانان من]            │
└──────────────────────────┘
┌──────────────────────────┐
│  معرفی‌نامه‌های فعال     │
│  (لیست)                  │
└──────────────────────────┘
```

#### **5. Quota Detail Screen**
- نمایش سهمیه به تفکیک مرکز
- Progress bar
- تاریخچه استفاده

#### **6. Guests Management**
```
┌──────────────────────────┐
│  [+ افزودن مهمان جدید]   │
└──────────────────────────┘
┌──────────────────────────┐
│  Guest Card 1            │
│  📝 نام: ...             │
│  🆔 کد ملی: ...         │
│  👥 نسبت: ...           │
│  [ویرایش] [حذف]         │
└──────────────────────────┘
┌──────────────────────────┐
│  [+ افزودن پرسنل]        │
│  (جستجو با کد پرسنلی)    │
└──────────────────────────┘
```

#### **7. New Letter Request Form**
```
Step 1: انتخاب مرکز
  [ ] مشهد (5 شب) - سهمیه: 2
  [ ] بابلسر (4 شب) - سهمیه: 1
  [ ] چادگان (3 شب) - سهمیه: 0

Step 2: انتخاب مهمانان
  [✓] خودم
  [✓] همسر
  [ ] فرزند 1
  [ ] ...
  جمع: 3 نفر

Step 3: تأیید
  مرکز: بابلسر
  افراد: 3 نفر
  نوع تعرفه: نرخ بانکی
  [✓] قوانین را مطالعه کردم

  [ثبت درخواست]
```

#### **8. Letter Detail Screen**
- اطلاعات معرفی‌نامه
- QR Code
- دکمه دانلود PDF
- دکمه اشتراک‌گذاری

#### **9. Letters List (تاریخچه)**
- فیلتر: همه / فعال / استفاده شده / لغو شده
- کارت‌های معرفی‌نامه
- کلیک → جزئیات

#### **10. Profile Screen**
- اطلاعات کاربری
- نمایش نقش
- ویرایش اطلاعات (محدود)
- تماس با پشتیبانی
- خروج (واقعاً لازم نیست چون در بله است)

---

## 🔌 **API Endpoints**

### **Authentication**
```
POST   /api/mini-app/auth/verify
  - Body: { initData: string }
  - Response: { token, user, personnel }
```

### **Personnel**
```
GET    /api/mini-app/personnel/me
POST   /api/mini-app/personnel/register
PATCH  /api/mini-app/personnel/update
```

### **Guests**
```
GET    /api/mini-app/guests
POST   /api/mini-app/guests
GET    /api/mini-app/guests/{id}
PATCH  /api/mini-app/guests/{id}
DELETE /api/mini-app/guests/{id}
```

### **Personnel Guests (پرسنل به عنوان همراه)**
```
GET    /api/mini-app/personnel-guests
GET    /api/mini-app/personnel-guests/search?employee_code=...
POST   /api/mini-app/personnel-guests
DELETE /api/mini-app/personnel-guests/{id}
```

### **Quotas**
```
GET    /api/mini-app/quotas
  - Response: { mashhad: {...}, babolsar: {...}, chadegan: {...} }
```

### **Letters**
```
GET    /api/mini-app/letters
POST   /api/mini-app/letters
GET    /api/mini-app/letters/{id}
DELETE /api/mini-app/letters/{id}/cancel
GET    /api/mini-app/letters/{id}/pdf
```

### **Centers**
```
GET    /api/mini-app/centers
```

---

## 🔐 **Authentication Flow (Bale OAuth)**

### **initData Structure**
Bale ارسال می‌کند:
```json
{
  "query_id": "...",
  "user": {
    "id": 123456789,
    "first_name": "علی",
    "last_name": "احمدی",
    "username": "ali_ahmadi",
    "language_code": "fa"
  },
  "auth_date": 1234567890,
  "hash": "abc123..."
}
```

### **Verification Steps**
1. دریافت `initData` از فرانت
2. Parse کردن داده‌ها
3. تأیید `hash` با Bale Bot Token
4. چک کردن `auth_date` (نباید قدیمی‌تر از 10 دقیقه باشد)
5. پیدا کردن/ساخت User با `bale_user_id`
6. صدور Sanctum Token
7. بازگشت Token + اطلاعات کاربر

### **Token Storage**
```javascript
// Frontend - localStorage
localStorage.setItem('auth_token', token)
localStorage.setItem('user', JSON.stringify(user))
```

### **Axios Interceptor**
```javascript
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})
```

---

## 📦 **Data Models (API Resources)**

### **User Resource**
```json
{
  "id": 1,
  "name": "علی احمدی",
  "email": "ali@bankmelli.ir",
  "bale_user_id": 123456789,
  "roles": ["user"],
  "created_at": "2024-01-01T12:00:00Z"
}
```

### **Personnel Resource**
```json
{
  "id": 1,
  "employee_code": "123456",
  "national_code": "1234567890",
  "full_name": "علی احمدی",
  "province": { "id": 1, "name": "تهران" },
  "status": "approved",
  "is_isargar": false,
  "service_years": 10,
  "family_members_count": 3,
  "quotas": {
    "mashhad": { "total": 2, "used": 0, "remaining": 2 },
    "babolsar": { "total": 1, "used": 1, "remaining": 0 },
    "chadegan": { "total": 1, "used": 0, "remaining": 1 }
  }
}
```

### **Guest Resource**
```json
{
  "id": 1,
  "national_code": "9876543210",
  "full_name": "فاطمه احمدی",
  "relation": "spouse",
  "birth_date": "1990-05-15",
  "gender": "female",
  "phone": "09123456789"
}
```

### **Introduction Letter Resource**
```json
{
  "id": 1,
  "letter_code": "L-2024-001234",
  "center": { "id": 1, "name": "زائرسرای مشهد" },
  "status": "active",
  "guests": [
    { "name": "علی احمدی", "national_code": "..." },
    { "name": "فاطمه احمدی", "national_code": "..." }
  ],
  "tariff_type": "bank_rate",
  "total_people": 2,
  "issue_date": "2024-02-01",
  "expiry_date": "2024-03-01",
  "qr_code": "base64...",
  "pdf_url": "/api/mini-app/letters/1/pdf"
}
```

---

## 🚀 **Implementation Steps**

### **Phase 1: Setup & Authentication (Week 1)**
- [x] ساخت پوشه `resources/mini-app/`
- [ ] Setup Vue 3 + Vite + Tailwind
- [ ] نصب Bale Mini App SDK
- [ ] پیاده‌سازی Authentication Backend
  - [ ] `MiniAppAuthController::verify()`
  - [ ] Bale initData verification service
  - [ ] Sanctum token generation
- [ ] پیاده‌سازی Authentication Frontend
  - [ ] Store: `auth.js` (Pinia)
  - [ ] Composable: `useAuth()`
  - [ ] View: `Login.vue`
- [ ] تست: ورود موفق و دریافت token

### **Phase 2: Personnel & Quotas (Week 1-2)**
- [ ] API: Personnel endpoints
  - [ ] `GET /personnel/me`
  - [ ] `POST /personnel/register`
  - [ ] `PATCH /personnel/update`
- [ ] API: Quota endpoints
  - [ ] `GET /quotas`
- [ ] Frontend:
  - [ ] View: `Welcome.vue` (اولین بار)
  - [ ] View: `PersonnelRegister.vue`
  - [ ] View: `PendingApproval.vue`
  - [ ] View: `Home.vue`
  - [ ] Component: `QuotaCard.vue`

### **Phase 3: Guests Management (Week 2)**
- [ ] API: Guest endpoints (همه CRUD)
- [ ] API: Personnel Guest endpoints
- [ ] Frontend:
  - [ ] View: `Guests.vue` (لیست)
  - [ ] View: `GuestForm.vue` (افزودن/ویرایش)
  - [ ] Component: `GuestCard.vue`
  - [ ] Component: `PersonnelGuestSearch.vue`

### **Phase 4: Introduction Letters (Week 2-3)**
- [ ] API: Letter endpoints
- [ ] API: PDF generation endpoint
- [ ] Frontend:
  - [ ] View: `Letters.vue` (لیست)
  - [ ] View: `LetterRequest.vue` (فرم چند مرحله‌ای)
  - [ ] View: `LetterDetail.vue`
  - [ ] Component: `CenterSelector.vue`
  - [ ] Component: `GuestSelector.vue`
  - [ ] Component: `LetterCard.vue`

### **Phase 5: Polish & Deploy (Week 3)**
- [ ] PWA configuration (Service Worker, manifest.json)
- [ ] Offline support (کش API responses)
- [ ] Error handling و Loading states
- [ ] تست کامل در موبایل
- [ ] بهینه‌سازی Bundle size
- [ ] Build production: `npm run build`
- [ ] Deploy: کپی `dist/` به `public/mini-app/`
- [ ] تنظیم در BotFather:
  ```
  /setmenubutton
  → انتخاب Bot
  → Mini App URL: https://ria.jafamhis.ir/welfare/mini-app/
  ```

---

## 🛠️ **Technical Details**

### **Bale Mini App SDK**
```html
<!-- در index.html -->
<script src="https://tapp-api.bale.ai/js/bale-miniapp.js"></script>
```

```javascript
// در main.js
import { BaleWebApp } from 'bale-mini-app-sdk'

// دریافت initData
const initData = BaleWebApp.initData
const initDataUnsafe = BaleWebApp.initDataUnsafe

// ارسال به backend برای verify
const response = await axios.post('/api/mini-app/auth/verify', {
  initData: initData
})

// ذخیره token
localStorage.setItem('auth_token', response.data.token)

// آماده نشان دادن Mini App
BaleWebApp.ready()

// بستن Mini App
BaleWebApp.close()

// ارسال داده به Bot
BaleWebApp.sendData(JSON.stringify({ action: 'letter_created', id: 123 }))
```

### **Backend - Verification Service**
```php
<?php

namespace App\Services;

class BaleVerificationService
{
    private string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.bale.bot_token');
    }

    public function verifyInitData(string $initData): array|false
    {
        // Parse initData
        parse_str($initData, $data);

        if (!isset($data['hash'])) {
            return false;
        }

        $checkHash = $data['hash'];
        unset($data['hash']);

        // Sort data
        ksort($data);

        // Create data-check-string
        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        $dataCheckString = implode("\n", $dataCheckArr);

        // Calculate secret key
        $secretKey = hash_hmac('sha256', $this->botToken, 'WebAppData', true);

        // Calculate hash
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        // Compare
        if (!hash_equals($calculatedHash, $checkHash)) {
            return false;
        }

        // Check auth_date (must be within 10 minutes)
        if (isset($data['auth_date'])) {
            $authDate = (int) $data['auth_date'];
            $now = time();
            if ($now - $authDate > 600) { // 10 minutes
                return false;
            }
        }

        // Parse user data
        if (isset($data['user'])) {
            $user = json_decode($data['user'], true);
            return $user;
        }

        return false;
    }
}
```

---

## ✅ **Testing Checklist**

### **Authentication**
- [ ] ورود موفق با Bale
- [ ] تأیید hash صحیح
- [ ] رد کردن hash نامعتبر
- [ ] رد کردن auth_date قدیمی
- [ ] ساخت User جدید برای بار اول
- [ ] لاگین User موجود

### **Personnel**
- [ ] ثبت‌نام پرسنل جدید
- [ ] وضعیت pending
- [ ] نمایش صفحه انتظار
- [ ] بروزرسانی به approved
- [ ] نمایش Home

### **Guests**
- [ ] لیست مهمانان
- [ ] افزودن مهمان
- [ ] ویرایش مهمان
- [ ] حذف مهمان
- [ ] افزودن پرسنل به عنوان همراه
- [ ] جستجوی پرسنل

### **Quotas**
- [ ] نمایش صحیح سهمیه‌ها
- [ ] محاسبه remaining
- [ ] بروزرسانی پس از استفاده

### **Letters**
- [ ] لیست معرفی‌نامه‌ها
- [ ] فیلتر بر اساس status
- [ ] ثبت درخواست جدید
- [ ] چک 3-year rule
- [ ] چک سهمیه کافی
- [ ] نمایش جزئیات
- [ ] دانلود PDF
- [ ] لغو معرفی‌نامه

### **Mobile UX**
- [ ] کار در اندروید
- [ ] کار در iOS
- [ ] دکمه‌های لمسی بزرگ (44x44 حداقل)
- [ ] اسکرول صاف
- [ ] فرم‌های راحت (اینپوت‌های بزرگ)
- [ ] Loading states
- [ ] Error messages واضح

---

## 📊 **Performance Targets**

- **First Contentful Paint**: < 1.5s
- **Time to Interactive**: < 3s
- **Bundle Size**: < 500KB (gzipped)
- **Lighthouse Score**: > 90

---

## 🔒 **Security Considerations**

1. **همیشه initData را در backend verify کنید**
2. **هرگز Bot Token را در فرانت قرار ندهید**
3. **Sanctum Token را فقط در HTTPS ارسال کنید**
4. **Rate limiting روی API endpoints**
5. **CORS فقط برای domain مشخص**
6. **Input validation در backend و frontend**
7. **Sanitize کردن user inputs**

---

## 📝 **Notes**

### **چرا Vue 3 به جای React؟**
- سبک‌تر (Bundle size کمتر)
- یادگیری ساده‌تر
- Performance عالی در موبایل
- Composition API شبیه React Hooks

### **چرا Tailwind؟**
- Utility-first → توسعه سریع
- Tree-shaking → فقط کلاس‌های استفاده شده
- Mobile-first به صورت پیش‌فرض
- Customization آسان

### **چرا PWA؟**
- کار offline (کش داده‌ها)
- Install در Home Screen
- Performance بهتر
- تجربه native-like

---

## 🎯 **Success Metrics**

- **Adoption Rate**: 60% کاربران از مینی‌اپ استفاده کنند
- **Task Completion**: 90% درخواست‌ها موفق ثبت شوند
- **User Satisfaction**: رضایت 4.5/5
- **Response Time**: میانگین < 2 ثانیه

---

**نسخه**: 1.0
**تاریخ**: 2024-02-14
**وضعیت**: آماده برای شروع پیاده‌سازی ✅
