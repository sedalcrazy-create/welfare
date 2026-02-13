<?php

namespace App\Services;

use App\Models\Personnel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PersonnelRequestService
{
    public function __construct(
        private UserQuotaService $quotaService
    ) {}

    /**
     * ایجاد درخواست جدید پرسنل
     */
    public function createRequest(array $data, User $user): Personnel
    {
        // بررسی سهمیه کاربر
        $center = \App\Models\Center::findOrFail($data['preferred_center_id']);

        if (!$this->quotaService->checkQuota($user, $center)) {
            throw new \RuntimeException('سهمیه کافی برای این مرکز ندارید');
        }

        // بررسی یکتا بودن کد ملی
        if (Personnel::where('national_code', $data['national_code'])->exists()) {
            throw new \RuntimeException('این کد ملی قبلاً ثبت شده است');
        }

        return DB::transaction(function () use ($data) {
            $personnel = Personnel::create([
                'employee_code' => $data['employee_code'],
                'full_name' => $data['full_name'],
                'national_code' => $data['national_code'],
                'phone' => $data['phone'],
                'preferred_center_id' => $data['preferred_center_id'],
                'preferred_period_id' => $data['preferred_period_id'],
                'family_members' => $data['family_members'] ?? null,
                'province_id' => $data['province_id'] ?? null,
                'bale_user_id' => $data['bale_user_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => Personnel::STATUS_PENDING,
                'registration_source' => $data['registration_source'] ?? Personnel::SOURCE_WEB,
                'tracking_code' => Personnel::generateTrackingCode(),
            ]);

            return $personnel;
        });
    }

    /**
     * بروزرسانی درخواست پرسنل
     */
    public function updateRequest(Personnel $personnel, array $data): Personnel
    {
        if ($personnel->status !== Personnel::STATUS_PENDING) {
            throw new \RuntimeException('فقط درخواست‌های در انتظار قابل ویرایش هستند');
        }

        // اگر کد ملی تغییر کرده، بررسی یکتا بودن
        if (isset($data['national_code']) && $data['national_code'] !== $personnel->national_code) {
            if (Personnel::where('national_code', $data['national_code'])->exists()) {
                throw new \RuntimeException('این کد ملی قبلاً ثبت شده است');
            }
        }

        return DB::transaction(function () use ($personnel, $data) {
            $personnel->update($data);
            return $personnel->fresh();
        });
    }

    /**
     * تأیید درخواست توسط ادمین
     */
    public function approve(Personnel $personnel, User $admin): Personnel
    {
        if ($personnel->status !== Personnel::STATUS_PENDING) {
            throw new \RuntimeException('فقط درخواست‌های در انتظار قابل تأیید هستند');
        }

        return DB::transaction(function () use ($personnel, $admin) {
            $personnel->update([
                'status' => Personnel::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            // ارسال notification به بات بله
            event(new \App\Events\PersonnelApproved($personnel));

            return $personnel->fresh();
        });
    }

    /**
     * رد درخواست توسط ادمین
     */
    public function reject(Personnel $personnel, User $admin, string $reason): Personnel
    {
        if ($personnel->status !== Personnel::STATUS_PENDING) {
            throw new \RuntimeException('فقط درخواست‌های در انتظار قابل رد هستند');
        }

        return DB::transaction(function () use ($personnel, $admin, $reason) {
            $personnel->update([
                'status' => Personnel::STATUS_REJECTED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'notes' => ($personnel->notes ? $personnel->notes . "\n\n" : '') . "دلیل رد: {$reason}",
            ]);

            // برگشت سهمیه (اگر قبلاً کسر شده بود - در فاز 1 نیاز نیست)
            // $this->quotaService->refundQuota($personnel->createdBy, $personnel->preferredCenter);

            // ارسال notification به بات بله
            event(new \App\Events\PersonnelRejected($personnel, $reason));

            return $personnel->fresh();
        });
    }

    /**
     * حذف درخواست
     */
    public function deleteRequest(Personnel $personnel): bool
    {
        if ($personnel->status !== Personnel::STATUS_PENDING) {
            throw new \RuntimeException('فقط درخواست‌های در انتظار قابل حذف هستند');
        }

        // بررسی اینکه معرفی‌نامه‌ای صادر نشده باشد
        if ($personnel->introductionLetters()->exists()) {
            throw new \RuntimeException('این درخواست دارای معرفی‌نامه است و قابل حذف نیست');
        }

        return DB::transaction(function () use ($personnel) {
            return $personnel->delete();
        });
    }

    /**
     * جستجوی درخواست با tracking code
     */
    public function findByTrackingCode(string $trackingCode): ?Personnel
    {
        return Personnel::where('tracking_code', $trackingCode)->first();
    }

    /**
     * جستجوی درخواست با کد ملی
     */
    public function findByNationalCode(string $nationalCode): ?Personnel
    {
        return Personnel::where('national_code', $nationalCode)->first();
    }

    /**
     * دریافت آمار درخواست‌ها
     */
    public function getStatistics(): array
    {
        return [
            'total' => Personnel::count(),
            'pending' => Personnel::pending()->count(),
            'approved' => Personnel::approved()->count(),
            'rejected' => Personnel::rejected()->count(),
            'from_web' => Personnel::where('registration_source', Personnel::SOURCE_WEB)->count(),
            'from_bale' => Personnel::fromBaleBot()->count(),
        ];
    }
}
