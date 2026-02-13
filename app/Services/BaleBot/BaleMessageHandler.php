<?php

namespace App\Services\BaleBot;

use App\Models\Center;
use App\Models\Personnel;
use App\Services\BaleBot\BaleService;
use App\Services\BaleBot\BaleSessionManager;
use App\Services\BaleBot\BaleRegistrationFlow;

/**
 * BaleMessageHandler - مدیریت پیام‌های متنی و دستورات بات
 */
class BaleMessageHandler
{
    public function __construct(
        private BaleService $baleService,
        private BaleSessionManager $sessionManager,
        private BaleRegistrationFlow $registrationFlow
    ) {}

    /**
     * پردازش پیام دریافتی
     */
    public function handle(
        int $chatId,
        int $userId,
        string $text,
        string $firstName,
        array $message
    ): void {
        // بررسی دستورات
        if (str_starts_with($text, '/')) {
            $this->handleCommand($chatId, $userId, $text, $firstName);
            return;
        }

        // بررسی اینکه آیا کاربر در حال ثبت‌نام است
        $currentStep = $this->sessionManager->getCurrentStep($userId);

        if ($currentStep) {
            $this->handleRegistrationStep($chatId, $userId, $text, $currentStep);
        } else {
            // پیام عادی - نمایش منوی راهنما
            $this->showMainMenu($chatId, $firstName);
        }
    }

    /**
     * مدیریت دستورات
     */
    private function handleCommand(int $chatId, int $userId, string $command, string $firstName): void
    {
        $command = strtolower(trim($command));

        match ($command) {
            '/start' => $this->handleStart($chatId, $userId, $firstName),
            '/help', '/راهنما' => $this->handleHelp($chatId),
            '/register', '/ثبت_نام' => $this->handleRegister($chatId, $userId),
            '/status', '/وضعیت' => $this->handleStatus($chatId, $userId),
            '/centers', '/مراکز' => $this->handleCenters($chatId),
            '/cancel', '/لغو' => $this->handleCancel($chatId, $userId),
            default => $this->handleUnknownCommand($chatId),
        };
    }

    /**
     * دستور /start - منوی اصلی
     */
    private function handleStart(int $chatId, int $userId, string $firstName): void
    {
        $welcomeText = "🌟 <b>سلام {$firstName} عزیز!</b>\n\n";
        $welcomeText .= "به سامانه مدیریت مراکز رفاهی بانک ملی ایران خوش آمدید.\n\n";
        $welcomeText .= "🏛️ <b>مراکز رفاهی ما:</b>\n";
        $welcomeText .= "  • زائرسرای مشهد مقدس (5 شب)\n";
        $welcomeText .= "  • متل بابلسر (4 شب)\n";
        $welcomeText .= "  • مرکز رفاهی چادگان (3 شب)\n\n";
        $welcomeText .= "یکی از گزینه‌های زیر را انتخاب کنید:";

        $keyboard = [
            [
                BaleService::makeInlineButton('📝 ثبت‌نام جدید', 'register_start'),
                BaleService::makeInlineButton('📊 وضعیت درخواست', 'check_status'),
            ],
            [
                BaleService::makeInlineButton('🏛️ مراکز رفاهی', 'view_centers'),
                BaleService::makeInlineButton('❓ راهنما', 'help'),
            ],
        ];

        $this->baleService->sendMessageWithInlineKeyboard($chatId, $welcomeText, $keyboard);
    }

    /**
     * دستور /help - راهنما
     */
    private function handleHelp(int $chatId): void
    {
        $helpText = "📚 <b>راهنمای استفاده از بات</b>\n\n";
        $helpText .= "<b>دستورات موجود:</b>\n\n";
        $helpText .= "/start - منوی اصلی\n";
        $helpText .= "/register - شروع ثبت‌نام جدید\n";
        $helpText .= "/status - بررسی وضعیت درخواست\n";
        $helpText .= "/centers - مشاهده مراکز رفاهی\n";
        $helpText .= "/cancel - لغو فرایند جاری\n";
        $helpText .= "/help - نمایش این راهنما\n\n";
        $helpText .= "💡 <b>نکته:</b> برای ثبت‌نام، دکمه «ثبت‌نام جدید» را بزنید.";

        $this->baleService->sendMessage($chatId, $helpText);
    }

    /**
     * دستور /register - شروع ثبت‌نام
     */
    private function handleRegister(int $chatId, int $userId): void
    {
        // شروع فرایند ثبت‌نام
        $this->sessionManager->startRegistration($userId);

        $text = "✅ <b>شروع ثبت‌نام</b>\n\n";
        $text .= "لطفاً <b>کد پرسنلی</b> خود را وارد کنید:\n\n";
        $text .= "💡 مثال: 12345\n\n";
        $text .= "برای لغو، /cancel را بزنید.";

        $this->baleService->sendMessage($chatId, $text);
    }

    /**
     * دستور /status - بررسی وضعیت
     */
    private function handleStatus(int $chatId, int $userId): void
    {
        $text = "🔍 <b>بررسی وضعیت درخواست</b>\n\n";
        $text .= "لطفاً <b>کد ملی</b> خود را وارد کنید:\n\n";
        $text .= "💡 مثال: 1234567890";

        // تنظیم step برای دریافت کد ملی
        $this->sessionManager->setCurrentStep($userId, 'awaiting_national_code_for_status');

        $this->baleService->sendMessage($chatId, $text);
    }

    /**
     * دستور /centers - نمایش مراکز
     */
    private function handleCenters(int $chatId): void
    {
        $centers = Center::where('is_active', true)->get();

        if ($centers->isEmpty()) {
            $this->baleService->sendMessage($chatId, "❌ در حال حاضر مرکزی فعال نیست.");
            return;
        }

        $text = "🏛️ <b>مراکز رفاهی فعال</b>\n\n";

        foreach ($centers as $center) {
            $typeLabel = match($center->type) {
                'religious' => '🕌 زیارتی',
                'beach' => '🏖️ ساحلی',
                'mountain' => '⛰️ کوهستانی',
                default => '🏛️',
            };

            $text .= "{$typeLabel} <b>{$center->name}</b>\n";
            $text .= "   📍 شهر: {$center->city}\n";
            $text .= "   🛏️ تعداد واحد: {$center->unit_count}\n";
            $text .= "   👥 ظرفیت: {$center->bed_count} تخت\n";
            $text .= "   ⏱️ مدت اقامت: {$center->stay_duration} شب\n\n";
        }

        $this->baleService->sendMessage($chatId, $text);
    }

    /**
     * دستور /cancel - لغو فرایند
     */
    private function handleCancel(int $chatId, int $userId): void
    {
        $this->sessionManager->clear($userId);

        $text = "❌ فرایند جاری لغو شد.\n\n";
        $text .= "برای شروع مجدد، /start را بزنید.";

        $this->baleService->sendMessage($chatId, $text);
    }

    /**
     * دستور نامشخص
     */
    private function handleUnknownCommand(int $chatId): void
    {
        $text = "❓ دستور نامعتبر.\n\n";
        $text .= "برای مشاهده دستورات موجود، /help را بزنید.";

        $this->baleService->sendMessage($chatId, $text);
    }

    /**
     * نمایش منوی اصلی
     */
    private function showMainMenu(int $chatId, string $firstName): void
    {
        $text = "سلام {$firstName} عزیز! 👋\n\n";
        $text .= "لطفاً یکی از گزینه‌های زیر را انتخاب کنید یا /help را بزنید.";

        $keyboard = [
            [
                BaleService::makeInlineButton('📝 ثبت‌نام', 'register_start'),
                BaleService::makeInlineButton('📊 وضعیت', 'check_status'),
            ],
        ];

        $this->baleService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    /**
     * مدیریت مراحل ثبت‌نام
     */
    private function handleRegistrationStep(int $chatId, int $userId, string $text, string $step): void
    {
        match ($step) {
            'awaiting_employee_code' => $this->processEmployeeCode($chatId, $userId, $text),
            'awaiting_full_name' => $this->processFullName($chatId, $userId, $text),
            'awaiting_national_code' => $this->processNationalCode($chatId, $userId, $text),
            'awaiting_phone' => $this->processPhone($chatId, $userId, $text),
            'awaiting_national_code_for_status' => $this->checkRequestStatus($chatId, $userId, $text),
            // Family member steps
            'awaiting_family_name' => $this->registrationFlow->processFamilyName($chatId, $userId, $text),
            'awaiting_family_national_code' => $this->registrationFlow->processFamilyNationalCode($chatId, $userId, $text),
            'awaiting_family_birth_date' => $this->registrationFlow->processFamilyBirthDate($chatId, $userId, $text),
            default => $this->baleService->sendMessage($chatId, "خطا در فرایند. لطفاً /cancel بزنید."),
        };
    }

    /**
     * پردازش کد پرسنلی
     */
    private function processEmployeeCode(int $chatId, int $userId, string $text): void
    {
        $employeeCode = trim($text);

        if (empty($employeeCode) || strlen($employeeCode) > 20) {
            $this->baleService->sendMessage($chatId, "❌ کد پرسنلی نامعتبر. لطفاً دوباره وارد کنید:");
            return;
        }

        $this->sessionManager->set($userId, 'employee_code', $employeeCode);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_full_name');

        $this->baleService->sendMessage(
            $chatId,
            "✅ کد پرسنلی ثبت شد.\n\nحالا لطفاً <b>نام و نام خانوادگی</b> خود را وارد کنید:\n\n💡 مثال: علی احمدی"
        );
    }

    /**
     * پردازش نام کامل
     */
    private function processFullName(int $chatId, int $userId, string $text): void
    {
        $fullName = trim($text);

        if (empty($fullName) || mb_strlen($fullName) < 3) {
            $this->baleService->sendMessage($chatId, "❌ نام نامعتبر. لطفاً دوباره وارد کنید:");
            return;
        }

        $this->sessionManager->set($userId, 'full_name', $fullName);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_national_code');

        $this->baleService->sendMessage(
            $chatId,
            "✅ نام ثبت شد.\n\nحالا لطفاً <b>کد ملی</b> خود را وارد کنید:\n\n💡 باید 10 رقم باشد"
        );
    }

    /**
     * پردازش کد ملی
     */
    private function processNationalCode(int $chatId, int $userId, string $text): void
    {
        // عادی‌سازی (حذف فاصله، تبدیل فارسی به انگلیسی)
        $nationalCode = MobileNumberNormalizer::normalizeDigits($text);

        if (strlen($nationalCode) !== 10 || !ctype_digit($nationalCode)) {
            $this->baleService->sendMessage($chatId, "❌ کد ملی باید دقیقاً 10 رقم باشد. دوباره وارد کنید:");
            return;
        }

        // بررسی تکراری بودن
        if (Personnel::where('national_code', $nationalCode)->exists()) {
            $this->baleService->sendMessage(
                $chatId,
                "❌ این کد ملی قبلاً ثبت شده است.\n\nبرای بررسی وضعیت، /status را بزنید."
            );
            $this->sessionManager->clear($userId);
            return;
        }

        $this->sessionManager->set($userId, 'national_code', $nationalCode);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_phone');

        $this->baleService->sendMessage(
            $chatId,
            "✅ کد ملی ثبت شد.\n\nحالا لطفاً <b>شماره موبایل</b> خود را وارد کنید:\n\n💡 مثال: 09123456789"
        );
    }

    /**
     * پردازش شماره موبایل
     */
    private function processPhone(int $chatId, int $userId, string $text): void
    {
        // عادی‌سازی شماره موبایل
        $phone = MobileNumberNormalizer::normalize($text);

        if (!$phone) {
            $this->baleService->sendMessage($chatId, "❌ شماره موبایل نامعتبر. دوباره وارد کنید:");
            return;
        }

        $this->sessionManager->set($userId, 'phone', $phone);
        $this->sessionManager->set($userId, 'bale_user_id', $userId);

        // حالا نمایش انتخاب مرکز (از طریق callback)
        $this->showCenterSelection($chatId, $userId);
    }

    /**
     * نمایش انتخاب مرکز
     */
    private function showCenterSelection(int $chatId, int $userId): void
    {
        $centers = Center::where('is_active', true)->get();

        $text = "✅ اطلاعات پایه ثبت شد!\n\n";
        $text .= "🏛️ حالا لطفاً <b>مرکز مورد نظر</b> خود را انتخاب کنید:";

        $keyboard = [];
        foreach ($centers as $center) {
            $emoji = match($center->type) {
                'religious' => '🕌',
                'beach' => '🏖️',
                'mountain' => '⛰️',
                default => '🏛️',
            };

            $keyboard[] = [
                BaleService::makeInlineButton(
                    "{$emoji} {$center->name}",
                    "select_center:{$center->id}"
                ),
            ];
        }

        $this->baleService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_center_selection');
    }

    /**
     * بررسی وضعیت درخواست
     */
    private function checkRequestStatus(int $chatId, int $userId, string $text): void
    {
        $nationalCode = MobileNumberNormalizer::normalizeDigits($text);

        if (strlen($nationalCode) !== 10) {
            $this->baleService->sendMessage($chatId, "❌ کد ملی باید 10 رقم باشد. دوباره وارد کنید:");
            return;
        }

        $personnel = Personnel::where('national_code', $nationalCode)->first();

        if (!$personnel) {
            $this->baleService->sendMessage(
                $chatId,
                "❌ درخواستی با این کد ملی یافت نشد.\n\nبرای ثبت‌نام جدید، /register را بزنید."
            );
            $this->sessionManager->clear($userId);
            return;
        }

        // نمایش وضعیت
        $statusText = "📊 <b>وضعیت درخواست شما</b>\n\n";
        $statusText .= "👤 نام: {$personnel->full_name}\n";
        $statusText .= "🆔 کد پیگیری: {$personnel->tracking_code}\n";
        $statusText .= "📅 تاریخ ثبت: " . jdate($personnel->created_at)->format('Y/m/d') . "\n\n";

        $statusText .= match($personnel->status) {
            'pending' => "⏳ <b>وضعیت: در انتظار بررسی</b>\n\nدرخواست شما در صف بررسی است.",
            'approved' => "✅ <b>وضعیت: تأیید شده</b>\n\nمعرفی‌نامه شما صادر شده است.",
            'rejected' => "❌ <b>وضعیت: رد شده</b>\n\nدلیل: {$personnel->rejection_reason}",
            default => "❓ وضعیت نامشخص",
        };

        $this->baleService->sendMessage($chatId, $statusText);
        $this->sessionManager->clear($userId);
    }
}
