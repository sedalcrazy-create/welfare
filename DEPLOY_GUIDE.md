# 🚀 راهنمای Deploy فایل آموزشی و اسکرین‌شات‌ها

**تاریخ:** 2026-02-12
**نسخه:** Phase 1

---

## 📋 فایل‌های آماده Deploy

### 1. فایل راهنمای کاربری (User Guide)
✅ `user-guide-standalone.html` - نسخه 2.0.0 Phase 1

### 2. اسکریپت اسکرین‌شات Phase 1
✅ `scripts/take-phase1-screenshots.js` - 7 اسکرین‌شات جدید

---

## 🌐 روش 1: Deploy فایل آموزشی به سرور

### گام 1: آپلود فایل به سرور

```bash
# از مسیر لوکال
cd E:/project/welfare-V2

# آپلود فایل user guide به سرور
scp user-guide-standalone.html root@37.152.174.87:/var/www/welfare/public/
```

### گام 2: دسترسی به فایل از مرورگر

بعد از آپلود، فایل در این آدرس قابل دسترسی است:
```
https://ria.jafamhis.ir/welfare/user-guide-standalone.html
```

---

## 📸 روش 2: گرفتن اسکرین‌شات‌های Phase 1

### روش A: اجرا روی سرور (توصیه می‌شود)

```bash
# 1. اتصال به سرور
ssh root@37.152.174.87

# 2. رفتن به مسیر پروژه
cd /var/www/welfare

# 3. آپلود اسکریپت جدید (از لوکال)
# از ترمینال لوکال:
scp scripts/take-phase1-screenshots.js root@37.152.174.87:/var/www/welfare/scripts/

# 4. بازگشت به سرور و نصب Playwright (اگر نصب نشده)
ssh root@37.152.174.87
cd /var/www/welfare
npm install @playwright/test@^1.40.0
npx playwright install chromium

# 5. اجرای اسکریپت
npm run screenshots:phase1

# 6. مشاهده نتیجه
ls -lh public/screenshots/phase1/
```

### روش B: اجرا روی لوکال

```bash
# از مسیر پروژه لوکال
cd E:/project/welfare-V2

# اجرای اسکریپت Phase 1
npm run screenshots:phase1

# آپلود اسکرین‌شات‌ها به سرور
scp -r public/screenshots/phase1/* root@37.152.174.87:/var/www/welfare/public/screenshots/phase1/
```

---

## 🖼️ اسکرین‌شات‌های Phase 1

اسکریپت 7 اسکرین‌شات زیر را می‌گیرد:

| # | فایل | توضیح |
|---|------|-------|
| 1 | `phase1-01-period-dropdown.png` | فرم ثبت درخواست با dropdown دوره (highlighted) |
| 2 | `phase1-02-approval-page-badge.png` | صفحه تأیید درخواست‌ها با badge pending |
| 3 | `phase1-03-filters-with-period.png` | فیلترهای صفحه تأیید شامل فیلتر دوره |
| 4 | `phase1-04-reject-modal.png` | Modal رد درخواست با textarea دلیل |
| 5 | `phase1-05-quota-per-center.png` | صفحه مدیریت سهمیه (کارت‌های per-center) |
| 6 | `phase1-06-increase-quota-modal.png` | Modal افزایش سهمیه |
| 7 | `phase1-07-bulk-operations.png` | چک‌باکس‌ها و دکمه‌های عملیات گروهی |

---

## 🔧 تنظیمات اسکریپت

### تغییر URL سرور

اگر URL متفاوت است، فایل `scripts/take-phase1-screenshots.js` را ویرایش کنید:

```javascript
const BASE_URL = 'https://ria.jafamhis.ir/welfare';  // تغییر دهید
```

### تغییر اعتبارنامه ادمین

```javascript
const ADMIN_EMAIL = 'admin@bankmelli.ir';  // تغییر دهید
const ADMIN_PASSWORD = 'password';         // تغییر دهید
```

### تغییر مسیر ذخیره

```javascript
const SCREENSHOT_DIR = path.join(__dirname, '..', 'public', 'screenshots', 'phase1');
```

---

## ✅ چک‌لیست Deploy

- [ ] آپلود `user-guide-standalone.html` به `/var/www/welfare/public/`
- [ ] تست دسترسی: https://ria.jafamhis.ir/welfare/user-guide-standalone.html
- [ ] نصب Playwright روی سرور (اگر نیاز باشد)
- [ ] آپلود `scripts/take-phase1-screenshots.js` به سرور
- [ ] اجرای اسکریپت اسکرین‌شات: `npm run screenshots:phase1`
- [ ] بررسی اسکرین‌شات‌ها در `public/screenshots/phase1/`
- [ ] Embed کردن اسکرین‌شات‌ها در `user-guide-standalone.html` (اختیاری)

---

## 🐛 عیب‌یابی

### مشکل: Playwright نصب نیست

```bash
npm install @playwright/test@^1.40.0
npx playwright install chromium
```

### مشکل: خطای HTTPS certificate

اسکریپت به صورت خودکار certificate errors را ignore می‌کند:
```javascript
ignoreHTTPSErrors: true
```

### مشکل: Login ناموفق

1. اطمینان از صحت `ADMIN_EMAIL` و `ADMIN_PASSWORD`
2. چک کردن `/login` route در سرور
3. بررسی CSRF token

### مشکل: عناصر پیدا نمی‌شوند

1. افزایش `waitForTimeout` در اسکریپت
2. استفاده از `headless: false` برای debug
3. چک کردن selector های موجود در صفحه

---

## 📦 Deploy کامل (All-in-One)

اسکریپت زیر تمام مراحل را به صورت خودکار انجام می‌دهد:

```bash
#!/bin/bash

echo "🚀 Starting Phase 1 deployment..."

# 1. Upload user guide
echo "📄 Uploading user guide..."
scp user-guide-standalone.html root@37.152.174.87:/var/www/welfare/public/

# 2. Upload screenshot script
echo "📸 Uploading screenshot script..."
scp scripts/take-phase1-screenshots.js root@37.152.174.87:/var/www/welfare/scripts/

# 3. Update package.json on server
echo "📦 Updating package.json..."
scp package.json root@37.152.174.87:/var/www/welfare/

# 4. Run screenshot script on server
echo "🎬 Taking screenshots..."
ssh root@37.152.174.87 << 'EOF'
  cd /var/www/welfare
  npm run screenshots:phase1
  ls -lh public/screenshots/phase1/
EOF

echo "✅ Deployment completed!"
echo "📍 User guide: https://ria.jafamhis.ir/welfare/user-guide-standalone.html"
echo "📸 Screenshots: /var/www/welfare/public/screenshots/phase1/"
```

ذخیره در `deploy-phase1.sh` و اجرا:

```bash
chmod +x deploy-phase1.sh
./deploy-phase1.sh
```

---

## 🔗 لینک‌های مفید

- **User Guide:** https://ria.jafamhis.ir/welfare/user-guide-standalone.html
- **API Base:** https://ria.jafamhis.ir/welfare/api/
- **Admin Panel:** https://ria.jafamhis.ir/welfare/admin/

---

**آخرین به‌روزرسانی:** 2026-02-12
**نسخه:** Phase 1 - v2.0.0
