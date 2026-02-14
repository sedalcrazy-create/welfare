<?php

namespace App\Services;

class BaleVerificationService
{
    private string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.bale.bot_token');
    }

    /**
     * Verify Bale Mini App initData
     *
     * @param string $initData
     * @return array|false Returns user data if valid, false otherwise
     */
    public function verifyInitData(string $initData): array|false
    {
        // Parse initData (format: key1=value1&key2=value2&...)
        parse_str($initData, $data);

        if (!isset($data['hash'])) {
            \Log::error('Bale verification failed: hash not found');
            return false;
        }

        $checkHash = $data['hash'];
        unset($data['hash']);

        // Sort data alphabetically
        ksort($data);

        // Create data-check-string (format: key1=value1\nkey2=value2\n...)
        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        $dataCheckString = implode("\n", $dataCheckArr);

        // Calculate secret key using HMAC-SHA-256
        $secretKey = hash_hmac('sha256', $this->botToken, 'WebAppData', true);

        // Calculate hash
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        // Compare hashes (timing-safe comparison)
        if (!hash_equals($calculatedHash, $checkHash)) {
            \Log::error('Bale verification failed: hash mismatch', [
                'expected' => $checkHash,
                'calculated' => $calculatedHash
            ]);
            return false;
        }

        // Check auth_date (must be within 10 minutes)
        if (isset($data['auth_date'])) {
            $authDate = (int) $data['auth_date'];
            $now = time();

            if ($now - $authDate > 600) { // 10 minutes = 600 seconds
                \Log::error('Bale verification failed: auth_date too old', [
                    'auth_date' => $authDate,
                    'now' => $now,
                    'diff' => $now - $authDate
                ]);
                return false;
            }
        }

        // Parse user data (JSON string)
        if (isset($data['user'])) {
            $user = json_decode($data['user'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Bale verification failed: invalid user JSON');
                return false;
            }

            return $user;
        }

        \Log::error('Bale verification failed: user data not found');
        return false;
    }

    /**
     * Extract user data from verified initData
     *
     * @param array $baleUser Verified Bale user data
     * @return array Normalized user data
     */
    public function extractUserData(array $baleUser): array
    {
        return [
            'bale_user_id' => $baleUser['id'] ?? null,
            'first_name' => $baleUser['first_name'] ?? null,
            'last_name' => $baleUser['last_name'] ?? null,
            'username' => $baleUser['username'] ?? null,
            'language_code' => $baleUser['language_code'] ?? 'fa',
        ];
    }
}
