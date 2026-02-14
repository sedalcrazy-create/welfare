<?php

namespace App\Services\BaleBot;

use Illuminate\Support\Facades\Redis;

/**
 * BaleSessionManager - مدیریت Session های موقت کاربران بات
 *
 * برای ذخیره داده‌های فرایند ثبت‌نام (multi-step form)
 */
class BaleSessionManager
{
    private const PREFIX = 'bale_session:';
    private const TTL = 1800; // 30 minutes

    /**
     * ذخیره داده session
     */
    public function set(int $userId, string $key, mixed $value): void
    {
        $sessionKey = $this->getSessionKey($userId);

        Redis::hset($sessionKey, $key, json_encode($value));
        Redis::expire($sessionKey, self::TTL);
    }

    /**
     * دریافت داده session
     */
    public function get(int $userId, string $key, mixed $default = null): mixed
    {
        $sessionKey = $this->getSessionKey($userId);
        $value = Redis::hget($sessionKey, $key);

        if ($value === null || $value === false) {
            return $default;
        }

        return json_decode($value, true);
    }

    /**
     * دریافت تمام داده‌های session
     */
    public function getAll(int $userId): array
    {
        $sessionKey = $this->getSessionKey($userId);
        $data = Redis::hgetall($sessionKey);

        if (empty($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = json_decode($value, true);
        }

        return $result;
    }

    /**
     * حذف یک کلید از session
     */
    public function forget(int $userId, string $key): void
    {
        $sessionKey = $this->getSessionKey($userId);
        Redis::hdel($sessionKey, $key);
    }

    /**
     * پاک کردن کامل session
     */
    public function clear(int $userId): void
    {
        $sessionKey = $this->getSessionKey($userId);
        Redis::del($sessionKey);
    }

    /**
     * چک کردن وجود session
     */
    public function exists(int $userId): bool
    {
        $sessionKey = $this->getSessionKey($userId);
        return Redis::exists($sessionKey) > 0;
    }

    /**
     * افزایش TTL (تمدید زمان)
     */
    public function extend(int $userId): void
    {
        $sessionKey = $this->getSessionKey($userId);
        Redis::expire($sessionKey, self::TTL);
    }

    /**
     * ساخت کلید session
     */
    private function getSessionKey(int $userId): string
    {
        return self::PREFIX . $userId;
    }

    // ===== Helper Methods برای فرایند ثبت‌نام =====

    /**
     * تنظیم مرحله فعلی ثبت‌نام
     */
    public function setCurrentStep(int $userId, string $step): void
    {
        $this->set($userId, 'current_step', $step);
    }

    /**
     * دریافت مرحله فعلی
     */
    public function getCurrentStep(int $userId): ?string
    {
        return $this->get($userId, 'current_step');
    }

    /**
     * ذخیره اطلاعات فرم
     */
    public function setFormData(int $userId, array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($userId, $key, $value);
        }
    }

    /**
     * دریافت اطلاعات فرم
     */
    public function getFormData(int $userId): array
    {
        return $this->getAll($userId);
    }

    /**
     * اضافه کردن یک همراه
     */
    public function addFamilyMember(int $userId, array $memberData): void
    {
        $members = $this->get($userId, 'family_members', []);
        $members[] = $memberData;
        $this->set($userId, 'family_members', $members);
    }

    /**
     * افزایش شمارنده همراهان (برای افزودن همراه بعدی)
     */
    public function incrementFamilyIndex(int $userId): void
    {
        $currentIndex = $this->get($userId, 'current_family_index', 0);
        $this->set($userId, 'current_family_index', $currentIndex + 1);
    }

    /**
     * دریافت لیست همراهان
     */
    public function getFamilyMembers(int $userId): array
    {
        return $this->get($userId, 'family_members', []);
    }

    /**
     * حذف یک همراه
     */
    public function removeFamilyMember(int $userId, int $index): void
    {
        $members = $this->get($userId, 'family_members', []);

        if (isset($members[$index])) {
            unset($members[$index]);
            $members = array_values($members); // reindex
            $this->set($userId, 'family_members', $members);
        }
    }

    /**
     * تنظیم مرکز انتخابی
     */
    public function setSelectedCenter(int $userId, int $centerId): void
    {
        $this->set($userId, 'selected_center_id', $centerId);
    }

    /**
     * دریافت مرکز انتخابی
     */
    public function getSelectedCenter(int $userId): ?int
    {
        return $this->get($userId, 'selected_center_id');
    }

    /**
     * تنظیم دوره انتخابی
     */
    public function setSelectedPeriod(int $userId, int $periodId): void
    {
        $this->set($userId, 'selected_period_id', $periodId);
    }

    /**
     * دریافت دوره انتخابی
     */
    public function getSelectedPeriod(int $userId): ?int
    {
        return $this->get($userId, 'selected_period_id');
    }

    /**
     * شروع فرایند ثبت‌نام جدید
     */
    public function startRegistration(int $userId): void
    {
        $this->clear($userId);
        $this->setCurrentStep($userId, 'awaiting_employee_code');
    }

    /**
     * چک کردن آماده بودن برای ثبت نهایی
     */
    public function isReadyForSubmit(int $userId): bool
    {
        $data = $this->getFormData($userId);

        $required = [
            'employee_code',
            'full_name',
            'national_code',
            'phone',
            'selected_center_id',
            'selected_period_id',
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * دریافت خلاصه اطلاعات برای نمایش
     */
    public function getSummary(int $userId): array
    {
        $data = $this->getFormData($userId);
        $familyMembers = $this->getFamilyMembers($userId);

        return [
            'employee_code' => $data['employee_code'] ?? null,
            'full_name' => $data['full_name'] ?? null,
            'national_code' => $data['national_code'] ?? null,
            'phone' => $data['phone'] ?? null,
            'selected_center_id' => $data['selected_center_id'] ?? null,
            'selected_period_id' => $data['selected_period_id'] ?? null,
            'family_members_count' => count($familyMembers),
            'family_members' => $familyMembers,
            'total_persons' => 1 + count($familyMembers),
        ];
    }
}
