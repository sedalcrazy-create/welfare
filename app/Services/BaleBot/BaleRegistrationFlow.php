<?php

namespace App\Services\BaleBot;

use App\Models\Personnel;
use App\Models\Center;
use App\Models\Period;
use App\Services\BaleBot\BaleService;
use App\Services\BaleBot\BaleSessionManager;
use Illuminate\Support\Str;

/**
 * BaleRegistrationFlow - مدیریت فرایند کامل ثبت‌نام Step-by-Step
 */
class BaleRegistrationFlow
{
    public function __construct(
        private BaleService $baleService,
        private BaleSessionManager $sessionManager
    ) {}

    /**
     * شروع ورود اطلاعات یک همراه
     */
    public function startAddingFamilyMember(int $chatId, int $userId): void
    {
        $currentIndex = $this->sessionManager->get($userId, 'current_family_index', 0);

        $text = "👤 <b>همراه شماره " . ($currentIndex + 1) . "</b>\n\n";
        $text .= "لطفاً <b>نام کامل</b> همراه را وارد کنید:\n\n";
        $text .= "💡 مثال: فاطمه محمدی";

        $this->baleService->sendMessage($chatId, $text);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_family_name');
    }

    /**
     * پردازش نام همراه
     */
    public function processFamilyName(int $chatId, int $userId, string $name): void
    {
        $this->sessionManager->set($userId, 'temp_family_name', $name);

        $text = "✅ نام ثبت شد.\n\n";
        $text .= "حالا <b>نسبت</b> این فرد با شما را انتخاب کنید:\n\n";
        $text .= "✅ <b>خانواده بانکی:</b> همسر، فرزند، پدر/مادر، پدر/مادر همسر\n";
        $text .= "⚠️ <b>متفرقه:</b> دوست، فامیل، سایر";

        $keyboard = [
            [
                BaleService::makeInlineButton('👫 همسر', 'family_relation:همسر'),
                BaleService::makeInlineButton('👶 فرزند', 'family_relation:فرزند'),
            ],
            [
                BaleService::makeInlineButton('👨 پدر', 'family_relation:پدر'),
                BaleService::makeInlineButton('👩 مادر', 'family_relation:مادر'),
            ],
            [
                BaleService::makeInlineButton('👨‍🦳 پدر همسر', 'family_relation:پدر همسر'),
                BaleService::makeInlineButton('👵 مادر همسر', 'family_relation:مادر همسر'),
            ],
            [
                BaleService::makeInlineButton('🤝 دوست', 'family_relation:دوست'),
                BaleService::makeInlineButton('👨‍👩‍👧 فامیل', 'family_relation:فامیل'),
            ],
            [
                BaleService::makeInlineButton('👥 سایر', 'family_relation:سایر'),
            ],
        ];

        $this->baleService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_family_relation');
    }

    /**
     * پردازش کد ملی همراه
     */
    public function processFamilyNationalCode(int $chatId, int $userId, string $nationalCode): void
    {
        // عادی‌سازی
        $nationalCode = MobileNumberNormalizer::normalizeDigits($nationalCode);

        if (strlen($nationalCode) !== 10 || !ctype_digit($nationalCode)) {
            $this->baleService->sendMessage($chatId, "❌ کد ملی باید 10 رقم باشد. دوباره وارد کنید:");
            return;
        }

        $this->sessionManager->set($userId, 'temp_family_national_code', $nationalCode);

        $text = "✅ کد ملی ثبت شد.\n\n";
        $text .= "لطفاً <b>تاریخ تولد</b> همراه را وارد کنید:\n\n";
        $text .= "💡 فرمت: 1370/05/15 (شمسی)";

        $this->baleService->sendMessage($chatId, $text);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_family_birth_date');
    }

    /**
     * پردازش تاریخ تولد همراه
     */
    public function processFamilyBirthDate(int $chatId, int $userId, string $birthDate): void
    {
        // Validation ساده تاریخ
        if (!preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $birthDate)) {
            $this->baleService->sendMessage(
                $chatId,
                "❌ فرمت تاریخ نادرست است. لطفاً به این صورت وارد کنید: 1370/05/15"
            );
            return;
        }

        $this->sessionManager->set($userId, 'temp_family_birth_date', $birthDate);

        $text = "✅ تاریخ تولد ثبت شد.\n\n";
        $text .= "حالا <b>جنسیت</b> را انتخاب کنید:";

        $keyboard = [
            [
                BaleService::makeInlineButton('👨 مرد', 'family_gender:male'),
                BaleService::makeInlineButton('👩 زن', 'family_gender:female'),
            ],
        ];

        $this->baleService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_family_gender');
    }

    /**
     * رد کردن ورود همراهان
     */
    public function skipFamilyMembers(int $chatId, int $userId): void
    {
        $this->sessionManager->set($userId, 'family_members_count', 0);
        $this->showFinalConfirmation($chatId, $userId);
    }

    /**
     * نمایش خلاصه نهایی و درخواست تأیید
     */
    public function showFinalConfirmation(int $chatId, int $userId): void
    {
        $summary = $this->sessionManager->getSummary($userId);

        $center = Center::find($summary['selected_center_id']);
        $period = Period::find($summary['selected_period_id']);

        $text = "📋 <b>خلاصه اطلاعات ثبت‌نام</b>\n\n";
        $text .= "━━━━━━━━━━━━━━━━━━\n";
        $text .= "👤 <b>اطلاعات شخصی:</b>\n";
        $text .= "   کد پرسنلی: {$summary['employee_code']}\n";
        $text .= "   نام: {$summary['full_name']}\n";
        $text .= "   کد ملی: {$summary['national_code']}\n";
        $text .= "   موبایل: {$summary['phone']}\n\n";

        $text .= "🏛️ <b>مرکز انتخابی:</b>\n";
        $text .= "   {$center->name}\n\n";

        if ($period) {
            $startDate = jdate($period->start_date)->format('Y/m/d');
            $endDate = jdate($period->end_date)->format('Y/m/d');
            $text .= "📅 <b>دوره اقامت:</b>\n";
            $text .= "   {$startDate} تا {$endDate}\n\n";
        }

        $text .= "👨‍👩‍👧‍👦 <b>همراهان:</b> {$summary['family_members_count']} نفر\n";

        if ($summary['family_members_count'] > 0) {
            $text .= "\n";
            foreach ($summary['family_members'] as $index => $member) {
                $text .= "   " . ($index + 1) . ". {$member['full_name']} ({$member['relation']})\n";
            }
        }

        $text .= "\n━━━━━━━━━━━━━━━━━━\n";
        $text .= "👥 <b>جمع کل افراد:</b> {$summary['total_persons']} نفر\n\n";
        $text .= "⚠️ لطفاً اطلاعات را بررسی کنید.";

        $keyboard = [
            [
                BaleService::makeInlineButton('✅ تأیید و ثبت نهایی', 'confirm_register'),
            ],
            [
                BaleService::makeInlineButton('❌ لغو', 'cancel_register'),
            ],
        ];

        $this->baleService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_final_confirmation');
    }

    /**
     * تأیید نهایی و ثبت در دیتابیس
     */
    public function confirmAndSubmit(int $chatId, int $userId): void
    {
        if (!$this->sessionManager->isReadyForSubmit($userId)) {
            $this->baleService->sendMessage($chatId, "❌ اطلاعات ناقص است. لطفاً دوباره شروع کنید.");
            $this->sessionManager->clear($userId);
            return;
        }

        $summary = $this->sessionManager->getSummary($userId);

        try {
            // ساخت tracking code یکتا
            $trackingCode = $this->generateTrackingCode();

            // ثبت در دیتابیس
            $personnel = Personnel::create([
                'employee_code' => $summary['employee_code'],
                'full_name' => $summary['full_name'],
                'national_code' => $summary['national_code'],
                'phone' => $summary['phone'],
                'bale_user_id' => $userId,
                'registration_source' => Personnel::SOURCE_BALE_BOT,
                'status' => Personnel::STATUS_PENDING,
                'tracking_code' => $trackingCode,
                'preferred_center_id' => $summary['selected_center_id'],
                'preferred_period_id' => $summary['selected_period_id'],
                'family_members' => $summary['family_members'],
                'family_count' => $summary['family_members_count'],
            ]);

            // پاک کردن session
            $this->sessionManager->clear($userId);

            // ارسال پیام موفقیت
            $successText = "🎉 <b>ثبت‌نام با موفقیت انجام شد!</b>\n\n";
            $successText .= "✅ درخواست شما با موفقیت ثبت شد و در صف بررسی قرار گرفت.\n\n";
            $successText .= "━━━━━━━━━━━━━━━━━━\n";
            $successText .= "🆔 <b>کد پیگیری شما:</b>\n";
            $successText .= "<code>{$trackingCode}</code>\n\n";
            $successText .= "━━━━━━━━━━━━━━━━━━\n\n";
            $successText .= "⏳ درخواست شما در حال بررسی توسط کارشناسان است.\n\n";
            $successText .= "📱 برای پیگیری وضعیت، دکمه «وضعیت درخواست» را بزنید یا /status را بفرستید.\n\n";
            $successText .= "💡 <b>نکته:</b> پس از تأیید، معرفی‌نامه شما در همین بات ارسال خواهد شد.";

            $keyboard = [
                [
                    BaleService::makeInlineButton('📊 بررسی وضعیت', 'check_status'),
                ],
                [
                    BaleService::makeInlineButton('🏠 منوی اصلی', 'register_start'),
                ],
            ];

            $this->baleService->sendMessageWithInlineKeyboard($chatId, $successText, $keyboard);

        } catch (\Exception $e) {
            \Log::error('Bale registration failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            $this->baleService->sendMessage(
                $chatId,
                "❌ خطا در ثبت اطلاعات. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید."
            );
        }
    }

    /**
     * تولید کد پیگیری یکتا
     */
    private function generateTrackingCode(): string
    {
        do {
            // فرمت: REQ-YYMM-XXXX
            $year = jdate()->format('y');
            $month = jdate()->format('m');
            $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $trackingCode = "REQ-{$year}{$month}-{$random}";

            // بررسی یکتا بودن
            $exists = Personnel::where('tracking_code', $trackingCode)->exists();
        } while ($exists);

        return $trackingCode;
    }
}
