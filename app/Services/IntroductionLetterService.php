<?php

namespace App\Services;

use App\Models\Personnel;
use App\Models\IntroductionLetter;
use App\Models\User;
use App\Models\Center;
use App\Models\Period;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class IntroductionLetterService
{
    public function __construct(
        private UserQuotaService $quotaService
    ) {}

    /**
     * صدور معرفی‌نامه
     */
    public function issue(Personnel $personnel, User $issuer): IntroductionLetter
    {
        // بررسی‌های اولیه
        if ($personnel->status !== Personnel::STATUS_APPROVED) {
            throw new \RuntimeException('فقط درخواست‌های تأیید شده قابل صدور معرفی‌نامه هستند');
        }

        if (!$personnel->preferred_center_id || !$personnel->preferred_period_id) {
            throw new \RuntimeException('مرکز یا دوره مشخص نشده است');
        }

        // بررسی اینکه قبلاً معرفی‌نامه صادر نشده باشد
        if ($this->hasActiveLetter($personnel)) {
            throw new \RuntimeException('برای این درخواست قبلاً معرفی‌نامه فعالی صادر شده است');
        }

        $center = Center::findOrFail($personnel->preferred_center_id);
        $period = Period::findOrFail($personnel->preferred_period_id);

        // بررسی سهمیه صادرکننده
        if (!$this->quotaService->checkQuota($issuer, $center)) {
            throw new \RuntimeException('سهمیه کافی برای صدور معرفی‌نامه ندارید');
        }

        // بررسی وضعیت دوره
        if ($period->status !== 'open') {
            throw new \RuntimeException('این دوره بسته شده و امکان صدور معرفی‌نامه وجود ندارد');
        }

        return DB::transaction(function () use ($personnel, $center, $period, $issuer) {
            // تولید کد معرفی‌نامه
            $letterCode = IntroductionLetter::generateLetterCode($center, $period);

            // ایجاد معرفی‌نامه
            $letter = IntroductionLetter::create([
                'letter_code' => $letterCode,
                'personnel_id' => $personnel->id,
                'center_id' => $center->id,
                'period_id' => $period->id,
                'issued_by_user_id' => $issuer->id,
                'family_count' => $personnel->family_count,
                'valid_from' => jdate($period->start_date)->format('Y/m/d'),
                'valid_until' => jdate($period->end_date)->format('Y/m/d'),
                'issued_at' => now(),
                'status' => 'active',
            ]);

            // کسر سهمیه
            $this->quotaService->consumeQuota($issuer, $center);

            // اینجا می‌توان notification ارسال کرد
            // event(new LetterIssued($letter));

            return $letter;
        });
    }

    /**
     * لغو معرفی‌نامه
     */
    public function cancel(IntroductionLetter $letter, User $user, string $reason): IntroductionLetter
    {
        if (!$letter->isActive()) {
            throw new \RuntimeException('فقط معرفی‌نامه‌های فعال قابل لغو هستند');
        }

        return DB::transaction(function () use ($letter, $user, $reason) {
            // لغو معرفی‌نامه
            $letter->cancel($reason, $user->id);

            // برگشت سهمیه
            $issuer = $letter->issuedBy;
            $center = $letter->center;

            $this->quotaService->refundQuota($issuer, $center);

            // اینجا می‌توان notification ارسال کرد
            // event(new LetterCancelled($letter));

            return $letter->fresh();
        });
    }

    /**
     * تولید PDF معرفی‌نامه
     */
    public function generatePDF(IntroductionLetter $letter)
    {
        $letter->load(['personnel', 'center', 'period', 'issuedBy']);

        $data = [
            'letter' => $letter,
            'personnel' => $letter->personnel,
            'center' => $letter->center,
            'period' => $letter->period,
            'family_members' => $letter->personnel->family_members ?? [],
            'qr_code' => $this->generateQRCode($letter),
        ];

        $pdf = Pdf::loadView('letters.pdf', $data);

        return $pdf->download("letter-{$letter->letter_code}.pdf");
    }

    /**
     * تولید QR Code برای معرفی‌نامه
     */
    private function generateQRCode(IntroductionLetter $letter): string
    {
        try {
            return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)
                ->generate($letter->letter_code);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * بررسی وجود معرفی‌نامه فعال
     */
    public function hasActiveLetter(Personnel $personnel): bool
    {
        return IntroductionLetter::where('personnel_id', $personnel->id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * دریافت معرفی‌نامه‌های یک پرسنل
     */
    public function getLettersByPersonnel(Personnel $personnel)
    {
        return IntroductionLetter::where('personnel_id', $personnel->id)
            ->with(['center', 'period', 'issuedBy'])
            ->orderBy('issued_at', 'desc')
            ->get();
    }

    /**
     * دریافت معرفی‌نامه‌های یک کاربر (صادرکننده)
     */
    public function getLettersByIssuer(User $user)
    {
        return IntroductionLetter::where('issued_by_user_id', $user->id)
            ->with(['personnel', 'center', 'period'])
            ->orderBy('issued_at', 'desc')
            ->get();
    }

    /**
     * جستجو با کد معرفی‌نامه
     */
    public function findByLetterCode(string $letterCode): ?IntroductionLetter
    {
        return IntroductionLetter::where('letter_code', $letterCode)
            ->with(['personnel', 'center', 'period', 'issuedBy'])
            ->first();
    }

    /**
     * علامت‌گذاری به عنوان استفاده شده
     */
    public function markAsUsed(IntroductionLetter $letter): IntroductionLetter
    {
        if (!$letter->isActive()) {
            throw new \RuntimeException('فقط معرفی‌نامه‌های فعال قابل علامت‌گذاری به عنوان استفاده شده هستند');
        }

        $letter->markAsUsed();

        return $letter->fresh();
    }

    /**
     * دریافت آمار معرفی‌نامه‌ها
     */
    public function getStatistics(): array
    {
        return [
            'total' => IntroductionLetter::count(),
            'active' => IntroductionLetter::active()->count(),
            'used' => IntroductionLetter::used()->count(),
            'cancelled' => IntroductionLetter::cancelled()->count(),
            'by_center' => IntroductionLetter::select('center_id', DB::raw('count(*) as count'))
                ->groupBy('center_id')
                ->with('center:id,name')
                ->get()
                ->pluck('count', 'center.name'),
        ];
    }
}
