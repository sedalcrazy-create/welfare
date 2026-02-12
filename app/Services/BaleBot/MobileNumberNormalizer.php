<?php

namespace App\Services\BaleBot;

class MobileNumberNormalizer
{
    /**
     * اعداد فارسی به انگلیسی
     */
    private static array $persianNumbers = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    /**
     * اعداد عربی به انگلیسی
     */
    private static array $arabicNumbers = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    /**
     * Normalize کردن شماره موبایل
     *
     * @param string $mobile
     * @return array ['valid' => bool, 'mobile' => string|null, 'message' => string]
     */
    public static function normalize(string $mobile): array
    {
        $original = $mobile;

        // 1. تبدیل اعداد فارسی به انگلیسی
        $mobile = str_replace(
            array_keys(self::$persianNumbers),
            array_values(self::$persianNumbers),
            $mobile
        );

        // 2. تبدیل اعداد عربی به انگلیسی
        $mobile = str_replace(
            array_keys(self::$arabicNumbers),
            array_values(self::$arabicNumbers),
            $mobile
        );

        // 3. حذف کاراکترهای غیرعددی (فاصله، خط تیره، پرانتز)
        $mobile = preg_replace('/[^\d+]/', '', $mobile);

        // 4. حذف + از ابتدا
        $mobile = ltrim($mobile, '+');

        // 5. تبدیل کد کشور 98 به 0
        if (preg_match('/^98(\d{10})$/', $mobile, $matches)) {
            // 98 9123456789 → 09123456789
            $mobile = '0' . $matches[1];
        }

        // 6. اگر 9 رقم بود و با 9 شروع شد، صفر اضافه کن
        if (preg_match('/^9\d{9}$/', $mobile)) {
            // 9123456789 → 09123456789
            $mobile = '0' . $mobile;
        }

        // 7. Validation نهایی
        if (!preg_match('/^09\d{9}$/', $mobile)) {
            return [
                'valid' => false,
                'mobile' => null,
                'message' => self::getErrorMessage($original, $mobile),
            ];
        }

        return [
            'valid' => true,
            'mobile' => $mobile,
            'message' => "✅ شماره موبایل: {$mobile}",
        ];
    }

    /**
     * پیام خطای دقیق بر اساس مشکل
     */
    private static function getErrorMessage(string $original, string $processed): string
    {
        $length = strlen($processed);

        if (empty($processed)) {
            return "❌ لطفاً شماره موبایل را وارد کنید";
        }

        if ($length < 11) {
            return "❌ شماره موبایل کوتاه است ({$length} رقم)\n" .
                   "💡 شماره باید 11 رقم باشد (مثال: 09123456789)";
        }

        if ($length > 11) {
            return "❌ شماره موبایل بلند است ({$length} رقم)\n" .
                   "💡 شماره باید 11 رقم باشد (مثال: 09123456789)";
        }

        if (!str_starts_with($processed, '09')) {
            return "❌ شماره موبایل باید با 09 شروع شود\n" .
                   "💡 شما وارد کردید: {$processed}\n" .
                   "مثال صحیح: 09123456789";
        }

        return "❌ فرمت شماره موبایل نادرست است\n" .
               "💡 مثال صحیح: 09123456789";
    }

    /**
     * چک کردن اینکه آیا شماره از Bale API آمده
     */
    public static function fromBaleContact($contact): ?string
    {
        if (empty($contact['phone_number'])) {
            return null;
        }

        $result = self::normalize($contact['phone_number']);

        return $result['valid'] ? $result['mobile'] : null;
    }

    /**
     * Log کردن شماره‌ها برای debug (masked)
     */
    public static function getMaskedMobile(string $mobile): string
    {
        if (strlen($mobile) < 11) {
            return '***';
        }

        // فقط 4 رقم اول و 2 رقم آخر نمایش داده می‌شود
        return substr($mobile, 0, 4) . '***' . substr($mobile, -2);
    }
}
