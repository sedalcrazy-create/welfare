<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBaleMessage;
use App\Jobs\ProcessBaleCallback;
use App\Services\BaleBot\BaleService;
use App\Services\BaleBot\BaleMessageHandler;
use App\Services\BaleBot\BaleCallbackHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * BaleWebhookController - کنترلر دریافت Webhook از بله
 *
 * این controller تمام پیام‌ها و callback های بات را مدیریت می‌کند
 */
class BaleWebhookController extends Controller
{
    public function __construct(
        private BaleService $baleService,
        private BaleMessageHandler $messageHandler,
        private BaleCallbackHandler $callbackHandler
    ) {}

    /**
     * دریافت و پردازش Webhook از بله
     */
    public function handle(Request $request, string $token): JsonResponse
    {
        // بررسی توکن
        if ($token !== config('services.bale.token')) {
            Log::channel('bale')->warning('Invalid webhook token attempt', [
                'provided_token' => $token,
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid token'], 403);
        }

        // دریافت داده‌های webhook
        $update = $request->all();

        Log::channel('bale')->info('Webhook received', $update);

        try {
            // پردازش بر اساس نوع update
            if (isset($update['message'])) {
                $this->processMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->processCallbackQuery($update['callback_query']);
            } elseif (isset($update['pre_checkout_query'])) {
                $this->processPreCheckout($update['pre_checkout_query']);
            } elseif (isset($update['successful_payment'])) {
                $this->processSuccessfulPayment($update['message']);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::channel('bale')->error('Webhook processing error', [
                'update' => $update,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * پردازش پیام‌های متنی (Async via Queue)
     */
    private function processMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $userId = $message['from']['id'] ?? null;
        $firstName = $message['from']['first_name'] ?? 'کاربر';

        // مدیریت دستورات و پیام‌های متنی (Async via Queue)
        // Typing action حذف شد برای سرعت بیشتر webhook
        ProcessBaleMessage::dispatch($chatId, $userId, $text, $firstName, $message);
    }

    /**
     * پردازش callback query ها (کلیک روی دکمه‌های inline) - Async
     */
    private function processCallbackQuery(array $callbackQuery): void
    {
        $callbackId = $callbackQuery['id'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $data = $callbackQuery['data'] ?? '';
        $userId = $callbackQuery['from']['id'];

        // پاسخ سریع به callback (sync - برای جلوگیری از timeout)
        $this->baleService->answerCallbackQuery($callbackId);

        // پردازش callback (Async via Queue)
        ProcessBaleCallback::dispatch($chatId, $messageId, $userId, $data);
    }

    /**
     * پردازش pre-checkout query (قبل از پرداخت)
     */
    private function processPreCheckout(array $preCheckout): void
    {
        $queryId = $preCheckout['id'];
        $payload = $preCheckout['invoice_payload'] ?? '';

        // می‌توانید اینجا بررسی‌های اضافی انجام دهید
        // مثلاً چک کردن موجودی سهمیه

        // تأیید پرداخت
        $this->baleService->callApi('answerPreCheckoutQuery', [
            'pre_checkout_query_id' => $queryId,
            'ok' => true,
        ]);

        Log::channel('bale')->info('Pre-checkout approved', [
            'query_id' => $queryId,
            'payload' => $payload,
        ]);
    }

    /**
     * پردازش پرداخت موفق
     */
    private function processSuccessfulPayment(array $message): void
    {
        $payment = $message['successful_payment'] ?? [];
        $chatId = $message['chat']['id'];

        Log::channel('bale')->info('Successful payment received', [
            'chat_id' => $chatId,
            'payment' => $payment,
        ]);

        // ارسال پیام تأیید
        $this->baleService->sendMessage(
            $chatId,
            "✅ پرداخت شما با موفقیت انجام شد.\n\n" .
            "💰 مبلغ: " . number_format($payment['total_amount']) . " ریال\n" .
            "🔖 شماره پیگیری: {$payment['telegram_payment_charge_id']}\n\n" .
            "از اعتماد شما سپاسگزاریم. 🙏"
        );

        // اینجا می‌توانید منطق بعد از پرداخت را اضافه کنید
        // مثلاً صدور معرفی‌نامه خودکار
    }
}
