# Phase 1 Deployment Guide

## Server Information
- **Server IP**: 37.152.174.87
- **Port**: 8083
- **Project Path**: (TBD - check on server)

## Pre-Deployment Checklist
- [x] All code committed to git (commit: 779e529)
- [x] Backend controllers and services implemented
- [x] Views created (admin quotas, personnel approvals)
- [x] Routes configured
- [x] Form requests with Persian validation
- [x] Mobile number normalizer for Bale bot
- [ ] Code pushed to server repository
- [ ] Migrations run on server
- [ ] Dependencies installed on server
- [ ] Cache cleared
- [ ] Tested on server

## Deployment Steps

### 1. Connect to Server
```bash
ssh user@37.152.174.87 -p 22
cd /path/to/welfare-V2
```

### 2. Pull Latest Changes
```bash
git pull origin main
```

### 3. Install Dependencies
```bash
docker-compose exec app composer install --no-dev --optimize-autoloader
```

### 4. Run Migrations
```bash
docker-compose exec app php artisan migrate
```

### 5. Clear Caches
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan optimize
```

### 6. Restart Services
```bash
docker-compose restart app
docker-compose restart queue
```

## Post-Deployment Testing

### Test 1: Quota Management
1. Login as admin
2. Navigate to user quota page
3. Allocate quota for a user and center
4. Verify quota display
5. Test increase/decrease/reset operations

### Test 2: Personnel Request (Web)
1. Navigate to `/personnel-requests/create`
2. Fill form with test data
3. **IMPORTANT**: Select a period (new Phase 1 requirement)
4. Add family members (optional)
5. Submit and verify tracking code is generated
6. Status should be "pending"

### Test 3: Admin Approval Workflow
1. Login as admin
2. Navigate to `/admin/personnel-approvals/pending`
3. View pending requests
4. Click on a request to view details
5. Test approve button → should redirect to letter issuance page
6. Test reject button → should ask for reason

### Test 4: Bale Bot API (Critical)
```bash
# Test mobile number normalization
curl -X POST http://37.152.174.87:8083/api/v1/personnel-requests/register \
  -H "Content-Type: application/json" \
  -d '{
    "employee_code": "12345",
    "full_name": "تست آزمایشی",
    "national_code": "1234567890",
    "phone": "۰۹۱۲۳۴۵۶۷۸۹",
    "preferred_center_id": 1,
    "preferred_period_id": 1
  }'

# Test with +98 format
curl -X POST http://37.152.174.87:8083/api/v1/personnel-requests/register \
  -H "Content-Type: application/json" \
  -d '{
    "employee_code": "12346",
    "full_name": "تست دوم",
    "national_code": "1234567891",
    "phone": "+989123456789",
    "preferred_center_id": 1,
    "preferred_period_id": 1
  }'
```

### Test 5: Centers and Periods API
```bash
# List centers
curl http://37.152.174.87:8083/api/v1/centers

# List periods
curl http://37.152.174.87:8083/api/v1/periods

# List periods for specific center
curl http://37.152.174.87:8083/api/v1/periods?center_id=1
```

## Rollback Plan
If deployment fails:
```bash
git log --oneline -5  # Find previous commit hash
git reset --hard <previous-commit-hash>
docker-compose exec app php artisan migrate:rollback --step=2
docker-compose restart app
```

## Phase 1 Completed Features

### ✅ Database & Models
- [x] `preferred_period_id` added to personnel table (REQUIRED)
- [x] `period_id` added to introduction_letters table
- [x] Personnel model updated with period relationship
- [x] IntroductionLetter generateLetterCode() supports periods

### ✅ Services
- [x] UserQuotaService - Complete quota management
- [x] PersonnelRequestService - Request lifecycle
- [x] IntroductionLetterService - Letter operations
- [x] MobileNumberNormalizer - Persian/English/Arabic support

### ✅ Controllers
- [x] Admin\QuotaController - 6 methods (index, allocate, update, reset, increase, decrease)
- [x] Admin\PersonnelApprovalController - 7 methods (pending, show, approve, reject, bulk approve/reject)
- [x] Api\CenterController - List centers
- [x] Api\PeriodController - List periods with filters
- [x] Api\PersonnelRequestController - Registration with mobile normalization

### ✅ Views
- [x] admin/quotas/index.blade.php - Beautiful quota management UI
- [x] admin/personnel-approvals/pending.blade.php - Pending list with filters
- [x] admin/personnel-approvals/show.blade.php - Detailed request view
- [x] personnel-requests/create.blade.php - Period selection added
- [x] personnel-requests/edit.blade.php - Period selection added

### ✅ Authorization & Validation
- [x] UserCenterQuotaPolicy - Quota authorization
- [x] 8 Form Requests with Persian error messages
- [x] Role-based access control

### ✅ Mobile Number Normalization
The MobileNumberNormalizer handles ALL these formats:
- ✅ Persian digits: `۰۹۱۲۳۴۵۶۷۸۹`
- ✅ Arabic digits: `٠٩١٢٣٤٥٦٧٨٩`
- ✅ With spaces: `0912 345 6789`
- ✅ With dashes: `0912-345-6789`
- ✅ Country code: `+989123456789` → `09123456789`
- ✅ Without leading zero: `9123456789` → `09123456789`

## Known Issues & Notes
- None currently identified
- All backend code complete
- All views created
- Ready for testing

## Support & Troubleshooting

### Common Issues

**Issue: Migration fails**
```bash
# Check migration status
docker-compose exec app php artisan migrate:status

# If stuck, try:
docker-compose exec app php artisan migrate:fresh --seed
```

**Issue: Route not found**
```bash
# Clear and recache routes
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan route:cache
```

**Issue: View not found**
```bash
# Clear view cache
docker-compose exec app php artisan view:clear
```

**Issue: Permission denied**
```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

## Next Steps After Successful Deployment
1. Test all workflows on server
2. Integrate with Bale Bot
3. Create sample data for demonstration
4. Train operators on new features
5. Monitor logs for errors:
   ```bash
   docker-compose logs -f app
   tail -f storage/logs/laravel.log
   ```

## Contact
For deployment issues, check:
- Git commit: `779e529`
- Date: 2026-02-12
- Phase: 1 - Introduction Letter System
