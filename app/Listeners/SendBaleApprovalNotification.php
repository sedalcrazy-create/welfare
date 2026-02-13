<?php

namespace App\Listeners;

use App\Events\PersonnelApproved;
use App\Services\BaleBot\BaleService;
use Illuminate\Support\Facades\Log;

/**
 * Listener to send Bale Bot notification when personnel request is approved
 */
class SendBaleApprovalNotification
{
    public function __construct(
        private BaleService $baleService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(PersonnelApproved $event): void
    {
        $personnel = $event->personnel;

        // Only send notification if registered via Bale Bot
        if (!$personnel->bale_user_id) {
            return;
        }

        try {
            $center = $personnel->preferredCenter;
            $period = $personnel->preferredPeriod;

            $message = "🎉 <b>تبریک! درخواست شما تأیید شد</b>\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "✅ درخواست رزرو شما با موفقیت تأیید شد.\n\n";
            $message .= "📋 <b>اطلاعات رزرو:</b>\n";
            $message .= "   کد پیگیری: <code>{$personnel->tracking_code}</code>\n";
            $message .= "   نام: {$personnel->full_name}\n";
            $message .= "   مرکز: {$center->name}\n";

            if ($period) {
                $startDate = jdate($period->start_date)->format('Y/m/d');
                $endDate = jdate($period->end_date)->format('Y/m/d');
                $message .= "   دوره اقامت: {$startDate} تا {$endDate}\n";
            }

            $message .= "\n━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📄 <b>معرفی‌نامه:</b>\n";
            $message .= "معرفی‌نامه شما توسط کارشناسان صادر شده و به زودی برای شما ارسال می‌شود.\n\n";
            $message .= "💡 <b>نکته مهم:</b> لطفاً معرفی‌نامه را هنگام مراجعه به مرکز همراه داشته باشید.\n\n";
            $message .= "📞 در صورت بروز هرگونه سوال یا مشکل، با پشتیبانی تماس بگیرید.\n\n";
            $message .= "🙏 سفری خوش و به یادماندنی را برای شما آرزومندیم.";

            $keyboard = [
                [
                    BaleService::makeInlineButton('📊 وضعیت درخواست', 'check_status'),
                ],
                [
                    BaleService::makeInlineButton('🏠 منوی اصلی', 'register_start'),
                ],
            ];

            $this->baleService->sendMessageWithInlineKeyboard(
                $personnel->bale_user_id,
                $message,
                $keyboard
            );

            Log::channel('bale')->info('Approval notification sent', [
                'personnel_id' => $personnel->id,
                'bale_user_id' => $personnel->bale_user_id,
                'tracking_code' => $personnel->tracking_code,
            ]);

        } catch (\Exception $e) {
            Log::channel('bale')->error('Failed to send approval notification', [
                'personnel_id' => $personnel->id,
                'bale_user_id' => $personnel->bale_user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
