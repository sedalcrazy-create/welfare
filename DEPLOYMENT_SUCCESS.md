# ✅ Phase 1 Successfully Deployed!

**تاریخ:** 2026-02-12
**سرور:** 37.152.174.87
**دامنه:** https://ria.jafamhis.ir/welfare/

---

## 🎉 خلاصه Deployment

### ✅ کارهای انجام شده

1. **انتقال فایل‌ها به سرور**
   - 32 فایل Phase 1 منتقل شد
   - Controllers, Services, Views, Migrations
   - حجم: 50 KB

2. **اجرای Migrations**
   ```
   ✅ 2026_02_12_000001_add_period_to_personnel (69.45ms)
   ✅ 2026_02_12_000002_add_period_to_introduction_letters (55.94ms)
   ```

3. **رفع باگ‌ها**
   - CenterController: `total_beds` → `bed_count`, `total_units` → `unit_count`
   - PeriodController: حذف ستون‌های `title` و `season_type`

4. **Cache Management**
   - Route cache cleared & rebuilt ✅
   - Config cache cleared & rebuilt ✅
   - View cache cleared & rebuilt ✅

5. **Restart Services**
   - Docker container restarted ✅
   - Application fully loaded ✅

---

## 🌐 URLs و Endpoints

### دامنه اصلی
```
https://ria.jafamhis.ir/welfare/
```

### API Endpoints (برای Bale Bot)

#### 1. لیست مراکز رفاهی
```bash
GET https://ria.jafamhis.ir/welfare/api/v1/centers
```

**نمونه پاسخ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "زائرسرای مشهد مقدس",
      "slug": "mashhad",
      "city": "مشهد",
      "type": "religious",
      "type_label": "زیارتی",
      "stay_duration": 5,
      "unit_count": 227,
      "bed_count": 1029,
      "description": "زائرسرای مشهد مقدس - زیارتی - مشهد (5 شب)"
    },
    {
      "id": 2,
      "name": "متل بابلسر",
      "slug": "babolsar",
      "city": "بابلسر",
      "type": "beach",
      "type_label": "ساحلی",
      "stay_duration": 4,
      "unit_count": 165,
      "bed_count": 626,
      "description": "متل بابلسر - ساحلی - بابلسر (4 شب)"
    },
    {
      "id": 3,
      "name": "مرکز رفاهی چادگان",
      "slug": "mrkz-rfahy-chadgan",
      "city": "چادگان",
      "type": "mountain",
      "type_label": "کوهستانی",
      "stay_duration": 3,
      "unit_count": 34,
      "bed_count": 126,
      "description": "مرکز رفاهی چادگان - کوهستانی - چادگان (3 شب)"
    }
  ],
  "total": 3
}
```

#### 2. لیست دوره‌های اقامت
```bash
GET https://ria.jafamhis.ir/welfare/api/v1/periods
GET https://ria.jafamhis.ir/welfare/api/v1/periods?center_id=1
GET https://ria.jafamhis.ir/welfare/api/v1/periods?status=open
```

#### 3. ثبت‌نام پرسنل (Bale Bot)
```bash
POST https://ria.jafamhis.ir/welfare/api/v1/personnel-requests/register
Content-Type: application/json

{
  "employee_code": "12345",
  "full_name": "علی احمدی",
  "national_code": "1234567890",
  "phone": "09123456789",
  "preferred_center_id": 1,
  "preferred_period_id": 1,
  "family_members": [
    {
      "full_name": "فاطمه احمدی",
      "relation": "همسر",
      "national_code": "9876543210",
      "gender": "female",
      "birth_date": "1370/05/15"
    }
  ]
}
```

**نکته مهم:** Mobile number normalizer تمام فرمت‌ها رو قبول می‌کنه:
- فارسی: `۰۹۱۲۳۴۵۶۷۸۹`
- انگلیسی: `09123456789`
- عربی: `٠٩١٢٣٤٥٦٧٨٩`
- با فاصله: `0912 345 6789`
- با کد کشور: `+989123456789`
- بدون صفر: `9123456789`

#### 4. چک کردن وضعیت درخواست
```bash
POST https://ria.jafamhis.ir/welfare/api/v1/personnel-requests/check-status
Content-Type: application/json

{
  "national_code": "1234567890",
  "phone": "09123456789"
}
```

#### 5. دریافت معرفی‌نامه‌ها
```bash
GET https://ria.jafamhis.ir/welfare/api/v1/personnel-requests/letters
Authorization: Bearer {token}
```

### Web Panel URLs

#### صفحه ورود
```
https://ria.jafamhis.ir/welfare/login
```

#### پنل مدیریت
```
https://ria.jafamhis.ir/welfare/dashboard
```

#### منوهای Phase 1
- **درخواست‌ها:** `https://ria.jafamhis.ir/welfare/personnel-requests`
- **تأیید درخواست‌ها:** `https://ria.jafamhis.ir/welfare/admin/personnel-approvals/pending`
- **معرفی‌نامه‌ها:** `https://ria.jafamhis.ir/welfare/introduction-letters`
- **مدیریت سهمیه:** `https://ria.jafamhis.ir/welfare/admin/user-center-quota`

---

## 🧪 تست‌های انجام شده

### ✅ API Tests
- [x] GET /api/v1/centers - 3 مرکز برگشت
- [x] GET /api/v1/periods - خالی (دیتا ندارد)
- [x] Web pages redirect به login

### ⏳ تست‌های باقیمانده

برای تست کامل باید این کارها انجام بشه:

#### 1. تست ثبت‌نام پرسنل
- [ ] Login به پنل
- [ ] رفتن به `/personnel-requests/create`
- [ ] پر کردن فرم با انتخاب **دوره** (جدید Phase 1)
- [ ] اضافه کردن همراه
- [ ] Submit و دریافت tracking code

#### 2. تست تأیید ادمین
- [ ] Login به عنوان admin
- [ ] رفتن به `/admin/personnel-approvals/pending`
- [ ] مشاهده لیست درخواست‌های pending
- [ ] تست فیلترها
- [ ] تأیید یک درخواست
- [ ] رد یک درخواست با دلیل

#### 3. تست مدیریت سهمیه
- [ ] رفتن به `/admin/user-center-quota`
- [ ] مشاهده سهمیه‌های کاربر
- [ ] افزایش سهمیه
- [ ] کاهش سهمیه
- [ ] ریست سهمیه

#### 4. تست Bale Bot Integration
```bash
# Test mobile normalization
curl -X POST https://ria.jafamhis.ir/welfare/api/v1/personnel-requests/register \
  -H "Content-Type: application/json" \
  -d '{
    "employee_code": "TEST001",
    "full_name": "تست بله",
    "national_code": "0123456789",
    "phone": "۰۹۱۲۳۴۵۶۷۸۹",
    "preferred_center_id": 1,
    "preferred_period_id": 1
  }'
```

#### 5. تست صدور معرفی‌نامه
- [ ] تأیید یک درخواست
- [ ] Redirect به صفحه صدور معرفی‌نامه
- [ ] صدور معرفی‌نامه
- [ ] چک کردن کد معرفی‌نامه (فرمت: MAS-0501-0001)
- [ ] مشاهده PDF معرفی‌نامه

---

## 📊 آمار Phase 1

### کدهای پیاده‌سازی شده
- **4 Controller جدید**: QuotaController, PersonnelApprovalController, CenterController, PeriodController
- **3 Service جدید**: UserQuotaService, PersonnelRequestService, IntroductionLetterService
- **1 Utility جدید**: MobileNumberNormalizer (برای Bale bot)
- **8 Form Request**: با پیام‌های خطای فارسی
- **3 Admin View**: quotas/index, personnel-approvals/pending, personnel-approvals/show
- **2 Migration**: اضافه کردن period_id
- **32 فایل** تغییر یافته
- **4,387 خط کد** اضافه شده

### ویژگی‌های پیاده‌سازی شده
✅ سیستم سهمیه مبتنی بر کاربر (هر کاربر، هر مرکز)
✅ انتخاب دوره در ثبت‌نام (الزامی Phase 1)
✅ ثبت‌نام با همراهان (حداکثر 10 نفر)
✅ گردش کار تأیید ادمین
✅ صدور معرفی‌نامه با مصرف سهمیه
✅ Mobile number normalizer (فارسی/انگلیسی/عربی)
✅ API کامل برای Bale bot
✅ مجوزدهی و کنترل دسترسی

---

## 🔧 Technical Details

### Database Changes
```sql
-- Migration 1: Add period to personnel
ALTER TABLE personnel ADD COLUMN preferred_period_id BIGINT;
ALTER TABLE personnel ADD CONSTRAINT personnel_preferred_period_id_foreign
  FOREIGN KEY (preferred_period_id) REFERENCES periods(id) ON DELETE SET NULL;

-- Migration 2: Add period to introduction_letters
ALTER TABLE introduction_letters ADD COLUMN period_id BIGINT;
ALTER TABLE introduction_letters ADD CONSTRAINT introduction_letters_period_id_foreign
  FOREIGN KEY (period_id) REFERENCES periods(id) ON DELETE SET NULL;
```

### Fixed Issues
1. **CenterController column mismatch:**
   - Database: `bed_count`, `unit_count`
   - Controller was using: `total_beds`, `total_units`
   - Fixed: Updated controller to use correct column names

2. **PeriodController column mismatch:**
   - Database doesn't have: `title`, `season_type`
   - Fixed: Removed non-existent columns from select

### Server Info
- **Path:** `/var/www/welfare`
- **Docker Containers:**
  - `welfare_app` - PHP-FPM + Nginx (Port 8083)
  - `welfare_postgres` - PostgreSQL 16 (Port 5434)
  - `welfare_redis` - Redis 7 (Port 6380)

---

## 📝 Next Steps

### Immediate (برای کامل کردن تست)
1. ✅ Deploy کامل شد
2. ⏳ تست manual workflows در پنل
3. ⏳ ایجاد دیتای نمونه برای دوره‌ها
4. ⏳ تست کامل Bale bot integration
5. ⏳ آموزش اپراتورها

### Future (Phase 2)
- سیستم قرعه‌کشی
- مدیریت رزروها
- گزارش‌گیری پیشرفته

---

## 🎯 Success Criteria

| معیار | وضعیت |
|-------|-------|
| Migration اجرا شد | ✅ |
| API مراکز کار می‌کند | ✅ |
| API دوره‌ها کار می‌کند | ✅ |
| Web panel قابل دسترسی | ✅ |
| HTTPS فعال است | ✅ |
| Domain mapping صحیح | ✅ |
| Container ها running هستند | ✅ |

---

## 🔐 Security Notes

- ✅ HTTPS فعال است
- ✅ Authentication middleware فعال
- ✅ Role-based authorization پیاده شده
- ✅ CSRF protection فعال
- ✅ SQL injection محافظت شده (Eloquent ORM)
- ⚠️ برای production باید `.env` را secure کرد

---

## 📞 Support

در صورت مشکل:
1. چک کردن logs: `tail -f /var/www/welfare/storage/logs/laravel.log`
2. چک کردن docker logs: `docker compose logs -f app`
3. Restart container: `docker compose restart app`

---

**🎉 Phase 1 با موفقیت Deploy شد و آماده تست است!**

**دامنه:** https://ria.jafamhis.ir/welfare/

**تاریخ Deploy:** 2026-02-12
**نسخه:** Phase 1 - Introduction Letter System
**Commit:** f28a3aa
