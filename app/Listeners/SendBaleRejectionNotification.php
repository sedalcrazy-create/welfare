<?php

namespace App\Listeners;

use App\Events\PersonnelRejected;
use App\Services\BaleBot\BaleService;
use Illuminate\Support\Facades\Log;

/**
 * Listener to send Bale Bot notification when personnel request is rejected
 */
class SendBaleRejectionNotification
{
    public function __construct(
        private BaleService $baleService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(PersonnelRejected $event): void
    {
        $personnel = $event->personnel;
        $reason = $event->reason;

        // Only send notification if registered via Bale Bot
        if (!$personnel->bale_user_id) {
            return;
        }

        try {
            $center = $personnel->preferredCenter;

            $message = "❌ <b>متأسفانه درخواست شما رد شد</b>\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "⚠️ درخواست رزرو شما پس از بررسی رد گردید.\n\n";
            $message .= "📋 <b>اطلاعات درخواست:</b>\n";
            $message .= "   کد پیگیری: <code>{$personnel->tracking_code}</code>\n";
            $message .= "   نام: {$personnel->full_name}\n";
            $message .= "   مرکز: {$center->name}\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📝 <b>دلیل رد:</b>\n";
            $message .= "{$reason}\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "💡 <b>توضیحات:</b>\n";
            $message .= "• شما می‌توانید با رفع مشکل مجدداً اقدام به ثبت درخواست کنید\n";
            $message .= "• برای اطلاعات بیشتر با پشتیبانی تماس بگیرید\n";
            $message .= "• در صورت نیاز، از طریق منوی اصلی درخواست جدید ثبت کنید\n\n";
            $message .= "📞 پشتیبانی: از طریق همین بات یا تماس با دفتر مرکزی";

            $keyboard = [
                [
                    BaleService::makeInlineButton('🔄 ثبت درخواست جدید', 'register_start'),
                ],
                [
                    BaleService::makeInlineButton('📞 تماس با پشتیبانی', 'contact_support'),
                    BaleService::makeInlineButton('❓ راهنما', 'help'),
                ],
            ];

            $this->baleService->sendMessageWithInlineKeyboard(
                $personnel->bale_user_id,
                $message,
                $keyboard
            );

            Log::channel('bale')->info('Rejection notification sent', [
                'personnel_id' => $personnel->id,
                'bale_user_id' => $personnel->bale_user_id,
                'tracking_code' => $personnel->tracking_code,
                'reason' => $reason,
            ]);

        } catch (\Exception $e) {
            Log::channel('bale')->error('Failed to send rejection notification', [
                'personnel_id' => $personnel->id,
                'bale_user_id' => $personnel->bale_user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
