# Bale Mini App - سامانه رفاهی بانک ملی

این مینی‌اپ برای استفاده پرسنل بانک ملی جهت مدیریت معرفی‌نامه‌های مراکز رفاهی ساخته شده است.

## 📱 ویژگی‌ها

- ✅ ورود خودکار با Bale (بدون username/password)
- ✅ ثبت‌نام اولیه پرسنل
- ✅ مدیریت مهمانان (افراد غیرپرسنل)
- ✅ مدیریت همراهان پرسنل (افراد پرسنل)
- ✅ صدور معرفی‌نامه با wizard 3 مرحله‌ای
- ✅ مشاهده سهمیه به تفکیک مرکز
- ✅ لیست و جزئیات معرفی‌نامه‌ها
- ✅ طراحی Mobile-First و تمام‌صفحه
- ✅ Navigation با Bottom Tab Bar
- ✅ PWA Support (نصب روی گوشی)

---

## 🚀 نصب و راه‌اندازی

### 1. نصب Dependencies

```bash
cd resources/mini-app
npm install
```

### 2. Development

```bash
npm run dev
```

سپس مرورگر را باز کنید: `http://localhost:5173`

### 3. Build برای Production

```bash
npm run build
```

فایل‌های build شده در `public/mini-app/` قرار می‌گیرند.

---

## 🔧 تنظیمات Bale Bot

### مرحله 1: باز کردن BotFather

1. در بله، به `@BotFather` پیام دهید
2. دستور `/mybots` را ارسال کنید
3. ربات خود را انتخاب کنید

### مرحله 2: تنظیم Mini App Button

```
/setmenubutton
→ انتخاب Bot
→ Mini App URL: https://ria.jafamhis.ir/welfare/mini-app/
→ Button Text: سامانه رفاهی
```

### مرحله 3: تست

1. ربات خود را در بله باز کنید
2. دکمه "سامانه رفاهی" (Menu Button) را کلیک کنید
3. مینی‌اپ باز می‌شود و ورود خودکار انجام می‌شود

---

## 📂 ساختار پروژه

```
resources/mini-app/
├── src/
│   ├── assets/           # فایل‌های استاتیک (CSS, fonts)
│   ├── components/       # کامپوننت‌های Vue
│   │   ├── common/       # دکمه‌ها، کارت‌ها، ...
│   │   └── layout/       # BottomNav, Header, ...
│   ├── composables/      # Vue composables (قابل استفاده مجدد)
│   ├── router/           # Vue Router تنظیمات
│   ├── stores/           # Pinia State Management
│   │   ├── auth.js       # احراز هویت
│   │   ├── personnel.js  # اطلاعات پرسنل
│   │   ├── guests.js     # مهمانان
│   │   └── letters.js    # معرفی‌نامه‌ها
│   ├── utils/            # توابع کمکی
│   │   └── axios.js      # HTTP Client با interceptors
│   ├── views/            # صفحات اصلی
│   │   ├── LoginView.vue
│   │   ├── RegisterView.vue
│   │   ├── PendingView.vue
│   │   ├── HomeView.vue
│   │   ├── GuestsView.vue
│   │   ├── LettersView.vue
│   │   ├── NewLetterView.vue
│   │   ├── LetterDetailView.vue
│   │   └── QuotasView.vue
│   ├── App.vue           # کامپوننت اصلی
│   └── main.js           # Entry point
├── index.html
├── package.json
├── vite.config.js
├── tailwind.config.js
└── README.md
```

---

## 🎨 طراحی UI/UX

### رنگ‌بندی

- **Primary**: `#00A6A6` (رنگ اصلی بله)
- **Secondary**: `#FF6B6B` (رنگ تأکیدی)
- **Success**: `#51CF66`
- **Warning**: `#FFD93D`
- **Danger**: `#FF6B6B`

### فونت

- **Vazirmatn** (متن‌های فارسی)
- پیش‌فرض سایز: `14px` (مناسب موبایل)

### دکمه‌ها

- **حداقل اندازه**: `44x44px` (touch-friendly)
- **Border Radius**: `8-12px` (گرد و نرم)
- **Active State**: `scale(0.95)` (فیدبک لمسی)

---

## 🔐 Authentication Flow

```
1. کاربر مینی‌اپ را از بله باز می‌کند
   ↓
2. Bale SDK ارسال می‌کند: initData
   ↓
3. Frontend ارسال می‌کند به: POST /api/mini-app/auth/verify
   ↓
4. Backend تأیید می‌کند: Bale initData hash
   ↓
5. Backend ایجاد می‌کند: Sanctum Token
   ↓
6. Frontend ذخیره می‌کند: Token در localStorage
   ↓
7. Routing بر اساس وضعیت:
   - بدون پرسنل → /register
   - پرسنل pending → /pending
   - پرسنل approved → /home (داشبورد)
```

---

## 📡 API Endpoints

### Authentication
- `POST /api/mini-app/auth/verify` - ورود با Bale initData
- `GET /api/mini-app/auth/me` - اطلاعات کاربر فعلی

### Personnel
- `GET /api/mini-app/personnel/me` - اطلاعات پرسنل + سهمیه
- `POST /api/mini-app/personnel/register` - ثبت‌نام پرسنل جدید
- `PATCH /api/mini-app/personnel/update` - بروزرسانی

### Guests (مهمانان)
- `GET /api/mini-app/guests` - لیست مهمانان
- `POST /api/mini-app/guests` - افزودن مهمان
- `PATCH /api/mini-app/guests/{id}` - ویرایش
- `DELETE /api/mini-app/guests/{id}` - حذف

### Personnel Guests (همراهان پرسنل)
- `GET /api/mini-app/personnel-guests` - لیست همراهان
- `GET /api/mini-app/personnel-guests/search?employee_code=...` - جستجو
- `POST /api/mini-app/personnel-guests` - افزودن
- `DELETE /api/mini-app/personnel-guests/{id}` - حذف

### Letters (معرفی‌نامه‌ها)
- `GET /api/mini-app/letters` - لیست معرفی‌نامه‌ها
- `POST /api/mini-app/letters` - صدور معرفی‌نامه جدید
- `GET /api/mini-app/letters/{id}` - جزئیات
- `DELETE /api/mini-app/letters/{id}/cancel` - لغو

### Centers & Quotas
- `GET /api/mini-app/centers` - لیست مراکز فعال
- `GET /api/mini-app/quotas` - سهمیه کاربر (به تفکیک مرکز)

---

## 🌐 Environment Variables

در `.env` اصلی پروژه:

```env
# Bale Bot Token (برای تأیید initData)
BALE_BOT_TOKEN=your_bot_token_here

# App URL (برای CORS)
APP_URL=https://ria.jafamhis.ir
```

---

## ⚡ Performance

### Bundle Size Optimization

- **Code Splitting**: Lazy loading برای routes
- **Tree Shaking**: فقط کدهای استفاده شده
- **Asset Optimization**: فونت‌ها و تصاویر بهینه

### PWA Features

- **Service Worker**: کش کردن فایل‌های استاتیک
- **Offline Support**: کار بدون اینترنت (محدود)
- **Install Prompt**: نصب روی صفحه اصلی گوشی

---

## 🐛 Debugging

### چک کردن Bale initData

```javascript
console.log(window.Bale?.WebApp?.initData)
```

### چک کردن Token

```javascript
console.log(localStorage.getItem('auth_token'))
```

### چک کردن User Data

```javascript
console.log(JSON.parse(localStorage.getItem('user')))
```

---

## 📱 تست در موبایل

### روش 1: Bale Bot (توصیه می‌شود)

1. ربات را در بله باز کنید
2. دکمه Menu را بزنید
3. مینی‌اپ باز می‌شود

### روش 2: مرورگر موبایل

1. در Chrome Mobile باز کنید: `https://ria.jafamhis.ir/welfare/mini-app/`
2. دکمه Menu → "Add to Home Screen"
3. از home screen باز کنید (مثل اپلیکیشن)

---

## 🔒 Security Checklist

- ✅ initData همیشه در backend تأیید می‌شود
- ✅ Bot Token هرگز در frontend قرار نمی‌گیرد
- ✅ Sanctum Token فقط در HTTPS ارسال می‌شود
- ✅ CORS فقط برای domain مشخص
- ✅ Input validation در backend و frontend
- ✅ XSS Protection با Vue (auto-escaping)

---

## 📞 پشتیبانی

در صورت بروز مشکل:

1. Console مرورگر را چک کنید (F12)
2. Network Tab را بررسی کنید
3. لاگ‌های Laravel را بخوانید: `storage/logs/laravel.log`

---

**نسخه**: 1.0.0
**تاریخ**: 2024-02-14
**وضعیت**: ✅ آماده برای استفاده
