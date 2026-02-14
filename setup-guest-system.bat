@echo off
chcp 65001 >nul
cls

echo ==================================================
echo 🚀 راه‌اندازی سیستم مدیریت مهمانان
echo ==================================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker در حال اجرا نیست!
    echo لطفاً ابتدا Docker Desktop را اجرا کنید.
    pause
    exit /b 1
)

echo ✅ Docker در حال اجرا است
echo.

REM Start containers
echo 📦 بررسی و راه‌اندازی کانتینرها...
cd /d "%~dp0"
docker-compose up -d

echo.
echo ⏳ منتظر آماده شدن دیتابیس...
timeout /t 5 /nobreak >nul

echo.
echo 🔄 اجرای migrations...
docker-compose exec -T app php artisan migrate --force

if errorlevel 1 (
    echo.
    echo ❌ خطا در اجرای migrations
    echo لطفاً خطاها را بررسی کنید.
    pause
    exit /b 1
)

echo.
echo ==================================================
echo ✅ نصب با موفقیت انجام شد!
echo ==================================================
echo.
echo 📊 جداول ایجاد شده:
echo   - guests (مهمانان)
echo   - personnel_guests (رابطه many-to-many)
echo   - lottery_entries.selected_guest_ids (فیلد جدید)
echo.
echo 🌐 دسترسی به پنل:
echo   - URL: http://localhost:8080
echo   - ایمیل: admin@bankmelli.ir
echo   - رمز: password
echo.
echo 📖 راهنما: GUEST_SYSTEM_GUIDE.md
echo ==================================================
echo.
pause
