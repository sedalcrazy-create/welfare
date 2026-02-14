<?php

namespace App\Services\BaleBot;

use App\Models\Center;
use App\Models\Period;
use App\Models\Personnel;
use App\Services\BaleBot\BaleService;
use App\Services\BaleBot\BaleSessionManager;
use App\Services\BaleBot\BaleRegistrationFlow;

/**
 * BaleCallbackHandler - مدیریت callback های دکمه‌های inline
 */
class BaleCallbackHandler
{
    public function __construct(
        private BaleService $baleService,
        private BaleSessionManager $sessionManager,
        private BaleRegistrationFlow $registrationFlow
    ) {}

    /**
     * پردازش callback query
     */
    public function handle(int $chatId, int $messageId, int $userId, string $data): void
    {
        // پارس کردن callback data
        [$action, $param] = $this->parseCallbackData($data);

        match ($action) {
            'register_start' => $this->handleRegisterStart($chatId, $userId),
            'check_status' => $this->handleCheckStatus($chatId, $userId),
            'view_centers' => $this->handleViewCenters($chatId),
            'help' => $this->handleHelp($chatId),
            'select_center' => $this->handleCenterSelection($chatId, $userId, (int)$param),
            'select_period' => $this->handlePeriodSelection($chatId, $userId, (int)$param),
            'family_count' => $this->handleFamilyCountSelection($chatId, $userId, (int)$param),
            'add_family_member' => $this->registrationFlow->startAddingFamilyMember($chatId, $userId),
            'skip_family' => $this->registrationFlow->skipFamilyMembers($chatId, $userId),
            'family_relation' => $this->handleFamilyRelation($chatId, $userId, $param),
            'family_gender' => $this->handleFamilyGender($chatId, $userId, $param),
            'add_another_family' => $this->handleAddAnotherFamily($chatId, $userId),
            'family_done' => $this->handleFamilyDone($chatId, $userId),
            'confirm_register' => $this->registrationFlow->confirmAndSubmit($chatId, $userId),
            'edit_info' => $this->handleEditInfo($chatId, $userId),
            'cancel_register' => $this->handleCancelRegistration($chatId, $userId),
            default => $this->handleUnknownCallback($chatId),
        };
    }

    /**
     * پارس callback data
     */
    private function parseCallbackData(string $data): array
    {
        if (str_contains($data, ':')) {
            return explode(':', $data, 2);
        }

        return [$data, null];
    }

    /**
     * شروع ثبت‌نام
     */
    private function handleRegisterStart(int $chatId, int $userId): void
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
     * چک کردن وضعیت
     */
    private function handleCheckStatus(int $chatId, int $userId): void
    {
        $text = "🔍 <b>بررسی وضعیت درخواست</b>\n\n";
        $text .= "لطفاً <b>کد ملی</b> خود را وارد کنید:\n\n";
        $text .= "💡 مثال: 1234567890";

        $this->sessionManager->setCurrentStep($userId, 'awaiting_national_code_for_status');

        $this->baleService->sendMessage($chatId, $text);
    }

    /**
     * نمایش مراکز
     */
    private function handleViewCenters(int $chatId): void
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
     * نمایش راهنما
     */
    private function handleHelp(int $chatId): void
    {
        $text = "📚 <b>راهنمای استفاده</b>\n\n";
        $text .= "برای ثبت‌نام جدید، دکمه «ثبت‌نام جدید» را بزنید.\n\n";
        $text .= "برای بررسی وضعیت درخواست خود، دکمه «وضعیت درخواست» را بزنید.\n\n";
        $text .= "💡 در هر مرحله می‌توانید با /cancel فرایند را لغو کنید.";

        $this->baleService->sendMessage($chatId, $text);
    }

    /**
     * انتخاب مرکز
     */
    private function handleCenterSelection(int $chatId, int $userId, int $centerId): void
    {
        $center = Center::find($centerId);

        if (!$center) {
            $this->baleService->sendMessage($chatId, "❌ مرکز یافت نشد.");
            return;
        }

        // ذخیره مرکز انتخابی
        $this->sessionManager->setSelectedCenter($userId, $centerId);

        $text = "✅ <b>{$center->name}</b> انتخاب شد.\n\n";
        $text .= "🗓️ حالا لطفاً <b>دوره اقامت</b> مورد نظر خود را انتخاب کنید:";

        // نمایش دوره‌های موجود برای این مرکز
        $periods = Period::where('center_id', $centerId)
            ->where('status', 'open')
            ->orderBy('start_date')
            ->get();

        if ($periods->isEmpty()) {
            $this->baleService->sendMessage(
                $chatId,
                "❌ متأسفانه در حال حاضر دوره‌ای برای این مرکز باز نیست.\n\nلطفاً مرکز دیگری انتخاب کنید."
            );
            return;
        }

        $keyboard = [];
        foreach ($periods as $period) {
            $startDate = jdate($period->start_date)->format('Y/m/d');
            $endDate = jdate($period->end_date)->format('Y/m/d');

            $keyboard[] = [
                BaleService::makeInlineButton(
                    "📅 {$startDate} تا {$endDate}",
                    "select_period:{$period->id}"
                ),
            ];
        }

        $this->baleService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_period_selection');
    }

    /**
     * انتخاب دوره
     */
    private function handlePeriodSelection(int $chatId, int $userId, int $periodId): void
    {
        $period = Period::find($periodId);

        if (!$period) {
            $this->baleService->sendMessage($chatId, "❌ دوره یافت نشد.");
            return;
        }

        // ذخیره دوره انتخابی
        $this->sessionManager->setSelectedPeriod($userId, $periodId);

        $startDate = jdate($period->start_date)->format('Y/m/d');
        $endDate = jdate($period->end_date)->format('Y/m/d');

        $text = "✅ دوره <b>{$startDate} تا {$endDate}</b> انتخاب شد.\n\n";
        $text .= "👨‍👩‍👧‍👦 حالا تعداد <b>همراهان</b> خود را مشخص کنید:\n\n";
        $text .= "💡 فقط خودتان را حساب نکنید (0 تا 10 نفر)";

        // دکمه‌های تعداد همراهان
        $keyboard = [
            [
                BaleService::makeInlineButton('0️⃣ بدون همراه', 'family_count:0'),
                BaleService::makeInlineButton('1️⃣', 'family_count:1'),
                BaleService::makeInlineButton('2️⃣', 'family_count:2'),
            ],
            [
                BaleService::makeInlineButton('3️⃣', 'family_count:3'),
                BaleService::makeInlineButton('4️⃣', 'family_count:4'),
                BaleService::makeInlineButton('5️⃣', 'family_count:5'),
            ],
            [
                BaleService::makeInlineButton('6️⃣', 'family_count:6'),
                BaleService::makeInlineButton('7️⃣', 'family_count:7'),
                BaleService::makeInlineButton('8️⃣', 'family_count:8'),
            ],
            [
                BaleService::makeInlineButton('9️⃣', 'family_count:9'),
                BaleService::makeInlineButton('🔟', 'family_count:10'),
            ],
        ];

        $this->baleService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_family_count');
    }

    /**
     * انتخاب تعداد همراهان
     */
    private function handleFamilyCountSelection(int $chatId, int $userId, int $count): void
    {
        $this->sessionManager->set($userId, 'family_members_count', $count);

        if ($count === 0) {
            // بدون همراه - رفتن به مرحله تأیید نهایی
            $this->registrationFlow->showFinalConfirmation($chatId, $userId);
        } else {
            // شروع ورود اطلاعات همراهان
            $this->sessionManager->set($userId, 'current_family_index', 0);
            $this->registrationFlow->startAddingFamilyMember($chatId, $userId);
        }
    }

    /**
     * انتخاب نسبت همراه
     */
    private function handleFamilyRelation(int $chatId, int $userId, string $relation): void
    {
        $this->sessionManager->set($userId, 'temp_family_relation', $relation);

        $text = "✅ نسبت ثبت شد.\n\n";
        $text .= "لطفاً <b>کد ملی</b> همراه را وارد کنید:\n\n";
        $text .= "💡 باید 10 رقم باشد";

        $this->baleService->sendMessage($chatId, $text);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_family_national_code');
    }

    /**
     * انتخاب جنسیت همراه
     */
    private function handleFamilyGender(int $chatId, int $userId, string $gender): void
    {
        // ذخیره اطلاعات کامل همراه
        $memberData = [
            'full_name' => $this->sessionManager->get($userId, 'temp_family_name'),
            'relation' => $this->sessionManager->get($userId, 'temp_family_relation'),
            'national_code' => $this->sessionManager->get($userId, 'temp_family_national_code'),
            'birth_date' => $this->sessionManager->get($userId, 'temp_family_birth_date'),
            'gender' => $gender,
        ];

        $this->sessionManager->addFamilyMember($userId, $memberData);

        // پاک کردن داده‌های موقت
        $this->sessionManager->forget($userId, 'temp_family_name');
        $this->sessionManager->forget($userId, 'temp_family_relation');
        $this->sessionManager->forget($userId, 'temp_family_national_code');
        $this->sessionManager->forget($userId, 'temp_family_birth_date');

        $currentIndex = $this->sessionManager->get($userId, 'current_family_index', 0);
        $registeredCount = count($this->sessionManager->get($userId, 'family_members', []));

        // نمایش منوی انتخاب بعد از ثبت هر همراه
        $text = "✅ همراه شماره " . $registeredCount . " ثبت شد!\n\n";
        $text .= "━━━━━━━━━━━━━━━━━━\n";

        // نمایش لیست همراهان ثبت شده
        $familyMembers = $this->sessionManager->get($userId, 'family_members', []);
        $text .= "👥 <b>همراهان ثبت شده:</b>\n\n";
        foreach ($familyMembers as $index => $member) {
            $isBankAffiliated = \App\Models\Personnel::isFamilyMemberBankAffiliated($member['relation'] ?? '');
            $badge = $isBankAffiliated ? '✅ بانکی' : '⚠️ غیر بانکی';
            $text .= ($index + 1) . ". {$member['full_name']} ({$member['relation']}) {$badge}\n";
        }

        $text .= "\n━━━━━━━━━━━━━━━━━━\n";
        $text .= "💡 می‌خواهید همراه دیگری اضافه کنید؟";

        $keyboard = [
            [
                BaleService::makeInlineButton('➕ افزودن همراه دیگر', 'add_another_family'),
            ],
            [
                BaleService::makeInlineButton('✅ تمام - ادامه فرایند', 'family_done'),
            ],
        ];

        $this->baleService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
        $this->sessionManager->setCurrentStep($userId, 'awaiting_family_action');
    }

    /**
     * افزودن همراه دیگر
     */
    private function handleAddAnotherFamily(int $chatId, int $userId): void
    {
        $this->sessionManager->incrementFamilyIndex($userId);
        $this->registrationFlow->startAddingFamilyMember($chatId, $userId);
    }

    /**
     * اتمام اضافه کردن همراهان
     */
    private function handleFamilyDone(int $chatId, int $userId): void
    {
        $this->registrationFlow->showFinalConfirmation($chatId, $userId);
    }

    /**
     * ویرایش اطلاعات
     */
    private function handleEditInfo(int $chatId, int $userId): void
    {
        $this->baleService->sendMessage(
            $chatId,
            "❌ برای ویرایش، لطفاً فرایند را لغو کرده (/cancel) و دوباره شروع کنید."
        );
    }

    /**
     * لغو ثبت‌نام
     */
    private function handleCancelRegistration(int $chatId, int $userId): void
    {
        $this->sessionManager->clear($userId);

        $text = "❌ ثبت‌نام لغو شد.\n\n";
        $text .= "برای شروع مجدد، /start را بزنید.";

        $this->baleService->sendMessage($chatId, $text);
    }

    /**
     * callback نامشخص
     */
    private function handleUnknownCallback(int $chatId): void
    {
        $this->baleService->sendMessage($chatId, "❓ دکمه نامعتبر.");
    }
}
