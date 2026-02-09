# Lottery System - سیستم قرعه‌کشی

## Why

سامانه مدیریت مراکز رفاهی بانک ملی ایران نیازمند یک سیستم قرعه‌کشی عادلانه برای توزیع ظرفیت محدود ۴۲۶ واحد اقامتی در ۳ مرکز رفاهی (مشهد، بابلسر، چادگان) بین حدود ۷۰,۰۰۰ پرسنل از ۳۷ استان/اداره امور است. این سیستم باید عدالت توزیعی را از طریق سهمیه‌بندی استانی و امتیازدهی اولویت تضمین کند.

## What Changes

- **Provincial Quota Distribution**: توزیع سهمیه متناسب با جمعیت پرسنل هر استان
- **Priority Scoring Algorithm**: الگوریتم امتیازدهی چندعاملی برای رتبه‌بندی شرکت‌کنندگان
- **Random Factor**: عنصر تصادفی برای جلوگیری از قطعیت نتایج
- **Waitlist Management**: مدیریت لیست انتظار با ارتقای خودکار
- **Provincial Approval Workflow**: فرآیند تأیید مدیران استانی
- **Unit Assignment**: تخصیص هوشمند واحدها بر اساس تعداد خانوار
- **Three-Year Rule Enforcement**: اعمال قانون ۳ سال برای نرخ بانکی

## Capabilities

### New Capabilities

- `lottery-lifecycle`: مدیریت چرخه عمر قرعه‌کشی (ایجاد، باز/بسته شدن، قرعه‌کشی، تکمیل)
- `priority-scoring`: الگوریتم محاسبه امتیاز اولویت شرکت‌کنندگان
- `quota-calculation`: محاسبه و توزیع سهمیه استانی
- `lottery-draw`: اجرای قرعه‌کشی و تعیین برندگان
- `waitlist-promotion`: مدیریت لیست انتظار و ارتقای خودکار
- `provincial-approval`: فرآیند تأیید/رد توسط مدیران استانی
- `unit-assignment`: تخصیص واحدهای اقامتی به برندگان تأیید شده
- `lottery-registration`: ثبت‌نام کاربران در قرعه‌کشی

### Modified Capabilities

_None - این یک سیستم جدید است_

## Impact

### Affected Code

- `app/Services/LotteryService.php` - سرویس اصلی قرعه‌کشی
- `app/Services/PriorityScoreService.php` - محاسبه امتیاز
- `app/Services/QuotaService.php` - محاسبه سهمیه
- `app/Models/Lottery.php` - مدل قرعه‌کشی
- `app/Models/LotteryEntry.php` - مدل شرکت‌کننده
- `app/Http/Controllers/LotteryController.php` - کنترلر وب
- `app/Http/Controllers/Api/LotteryController.php` - کنترلر API

### APIs

- `POST /api/lotteries/{id}/enter` - ثبت‌نام در قرعه‌کشی
- `DELETE /api/lotteries/{id}/cancel` - لغو ثبت‌نام
- `GET /api/lotteries/{id}/results` - مشاهده نتایج
- `POST /api/admin/lotteries/{id}/draw` - اجرای قرعه‌کشی
- `POST /api/provincial/entries/{id}/approve` - تأیید برنده
- `POST /api/provincial/entries/{id}/reject` - رد برنده

### Dependencies

- PostgreSQL برای ذخیره‌سازی تراکنشی
- Redis برای کش سهمیه‌ها
- Spatie Permission برای کنترل دسترسی

### Systems

- پنل مدیریت وب
- اپلیکیشن موبایل Bale
- سیستم گزارش‌گیری
