<?php

namespace App\Services\BaleBot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BaleService - سرویس ارتباط با Bale Bot API
 *
 * این سرویس مسئول تمام ارتباطات با API بله است
 */
class BaleService
{
    private string $botToken;
    private string $apiBaseUrl;

    public function __construct()
    {
        $this->botToken = config('services.bale.token');
        $this->apiBaseUrl = config('services.bale.api_url', 'https://tapi.bale.ai/bot');
    }

    /**
     * ارسال پیام متنی ساده
     */
    public function sendMessage(int $chatId, string $text, ?array $replyMarkup = null): array
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->callApi('sendMessage', $params);
    }

    /**
     * ارسال پیام با دکمه‌های Inline
     */
    public function sendMessageWithInlineKeyboard(
        int $chatId,
        string $text,
        array $inlineKeyboard
    ): array {
        return $this->sendMessage($chatId, $text, [
            'inline_keyboard' => $inlineKeyboard,
        ]);
    }

    /**
     * ارسال عکس
     */
    public function sendPhoto(
        int $chatId,
        string $photoUrl,
        ?string $caption = null,
        ?array $replyMarkup = null
    ): array {
        $params = [
            'chat_id' => $chatId,
            'photo' => $photoUrl,
        ];

        if ($caption) {
            $params['caption'] = $caption;
        }

        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->callApi('sendPhoto', $params);
    }

    /**
     * ویرایش متن پیام
     */
    public function editMessageText(
        int $chatId,
        int $messageId,
        string $text,
        ?array $replyMarkup = null
    ): array {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ];

        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->callApi('editMessageText', $params);
    }

    /**
     * ویرایش صفحه‌کلید inline پیام
     */
    public function editMessageReplyMarkup(
        int $chatId,
        int $messageId,
        array $replyMarkup
    ): array {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => json_encode($replyMarkup),
        ];

        return $this->callApi('editMessageReplyMarkup', $params);
    }

    /**
     * پاسخ به callback query (کلیک روی دکمه inline)
     */
    public function answerCallbackQuery(
        string $callbackQueryId,
        ?string $text = null,
        bool $showAlert = false
    ): array {
        $params = [
            'callback_query_id' => $callbackQueryId,
        ];

        if ($text) {
            $params['text'] = $text;
            $params['show_alert'] = $showAlert;
        }

        return $this->callApi('answerCallbackQuery', $params);
    }

    /**
     * تنظیم Webhook
     */
    public function setWebhook(string $webhookUrl): array
    {
        return $this->callApi('setWebhook', [
            'url' => $webhookUrl,
        ]);
    }

    /**
     * حذف Webhook
     */
    public function deleteWebhook(): array
    {
        return $this->callApi('deleteWebhook');
    }

    /**
     * دریافت اطلاعات Webhook
     */
    public function getWebhookInfo(): array
    {
        return $this->callApi('getWebhookInfo');
    }

    /**
     * ارسال اکشن (typing, upload_photo, etc.)
     */
    public function sendChatAction(int $chatId, string $action = 'typing'): array
    {
        return $this->callApi('sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action,
        ]);
    }

    /**
     * ارسال درخواست پول (Invoice)
     */
    public function sendInvoice(
        int $chatId,
        string $title,
        string $description,
        string $payload,
        string $providerToken,
        array $prices,
        ?string $photoUrl = null
    ): array {
        $params = [
            'chat_id' => $chatId,
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'provider_token' => $providerToken,
            'prices' => json_encode($prices),
        ];

        if ($photoUrl) {
            $params['photo_url'] = $photoUrl;
        }

        return $this->callApi('sendInvoice', $params);
    }

    /**
     * فراخوانی API بله
     */
    private function callApi(string $method, array $params = []): array
    {
        $url = "{$this->apiBaseUrl}{$this->botToken}/{$method}";

        try {
            $response = Http::timeout(30)
                ->post($url, $params);

            $result = $response->json();

            // لاگ برای debug
            Log::channel('bale')->info("Bale API Call: {$method}", [
                'params' => $params,
                'response' => $result,
            ]);

            if (!$result['ok'] ?? false) {
                Log::channel('bale')->error("Bale API Error: {$method}", [
                    'params' => $params,
                    'response' => $result,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::channel('bale')->error("Bale API Exception: {$method}", [
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Helper: ساخت دکمه Inline
     */
    public static function makeInlineButton(string $text, string $callbackData): array
    {
        return [
            'text' => $text,
            'callback_data' => $callbackData,
        ];
    }

    /**
     * Helper: ساخت دکمه URL
     */
    public static function makeUrlButton(string $text, string $url): array
    {
        return [
            'text' => $text,
            'url' => $url,
        ];
    }

    /**
     * Helper: ساخت دکمه Web App
     */
    public static function makeWebAppButton(string $text, string $webAppUrl): array
    {
        return [
            'text' => $text,
            'web_app' => ['url' => $webAppUrl],
        ];
    }

    /**
     * Helper: ساخت صفحه‌کلید Inline با چند ردیف
     */
    public static function makeInlineKeyboard(array $rows): array
    {
        return ['inline_keyboard' => $rows];
    }
}
