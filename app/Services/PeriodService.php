<?php

namespace App\Services;

use App\Models\Period;
use App\Models\Center;

class PeriodService
{
    public function __construct(
        private DateService $dateService
    ) {}

    /**
     * تولید کد یکتا برای دوره
     */
    public function generatePeriodCode(Center $center, string $startDate): string
    {
        $prefix = match($center->slug) {
            'mashhad' => 'MSH',
            'babolsar' => 'BAB',
            'chadegan' => 'CHD',
            default => strtoupper(substr($center->slug ?? 'UNK', 0, 3)),
        };

        $year = substr($startDate, 0, 4);
        $month = substr($startDate, 5, 2);

        // شمارنده
        $count = Period::where('center_id', $center->id)
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->count() + 1;

        return "{$prefix}-{$year}{$month}-" . str_pad($count, 2, '0', STR_PAD_LEFT);
    }

    /**
     * ایجاد دوره جدید
     */
    public function createPeriod(array $data): Period
    {
        $startDate = $this->dateService->jalaliToGregorian($data['jalali_start_date']);
        $endDate = $this->dateService->jalaliToGregorian($data['jalali_end_date']);

        $center = Center::find($data['center_id']);
        $code = $this->generatePeriodCode($center, $startDate);

        return Period::create([
            'center_id' => $data['center_id'],
            'season_id' => $data['season_id'] ?: null,
            'code' => $code,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'jalali_start_date' => $data['jalali_start_date'],
            'jalali_end_date' => $data['jalali_end_date'],
            'capacity' => $data['capacity'],
            'reserved_count' => 0,
            'status' => $data['status'],
        ]);
    }

    /**
     * به‌روزرسانی دوره
     */
    public function updatePeriod(Period $period, array $data): Period
    {
        $startDate = $this->dateService->jalaliToGregorian($data['jalali_start_date']);
        $endDate = $this->dateService->jalaliToGregorian($data['jalali_end_date']);

        // اگر مرکز عوض شد، کد جدید تولید کن
        $code = $period->code;
        if ($period->center_id !== (int)$data['center_id']) {
            $center = Center::find($data['center_id']);
            $code = $this->generatePeriodCode($center, $startDate);
        }

        $period->update([
            'center_id' => $data['center_id'],
            'season_id' => $data['season_id'] ?: null,
            'code' => $code,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'jalali_start_date' => $data['jalali_start_date'],
            'jalali_end_date' => $data['jalali_end_date'],
            'capacity' => $data['capacity'],
            'status' => $data['status'],
        ]);

        return $period;
    }

    /**
     * بررسی اعتبار تاریخ‌ها
     */
    public function validateDates(string $jalaliStartDate, string $jalaliEndDate): bool
    {
        $startDate = $this->dateService->jalaliToGregorian($jalaliStartDate);
        $endDate = $this->dateService->jalaliToGregorian($jalaliEndDate);

        return $endDate > $startDate;
    }

    /**
     * بررسی امکان ویرایش دوره
     */
    public function canUpdate(Period $period): bool
    {
        if (!$period->lottery) {
            return true;
        }

        return $period->lottery->status === 'draft';
    }

    /**
     * بررسی امکان حذف دوره
     */
    public function canDelete(Period $period): array
    {
        if ($period->reservations()->count() > 0) {
            return [false, 'این دوره دارای رزرو است و قابل حذف نیست.'];
        }

        if ($period->lottery) {
            return [false, 'این دوره دارای قرعه‌کشی است و قابل حذف نیست.'];
        }

        return [true, null];
    }
}
