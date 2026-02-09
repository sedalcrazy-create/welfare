<?php

namespace App\Services;

use App\Models\Lottery;
use App\Models\LotteryEntry;
use App\Models\Period;
use App\Models\Province;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LotteryService
{
    public function __construct(
        private PriorityScoreService $priorityScoreService,
        private QuotaService $quotaService,
    ) {}

    public function draw(Lottery $lottery): array
    {
        $period = $lottery->period;
        $entries = $lottery->entries()->where('status', LotteryEntry::STATUS_PENDING)->get();

        if ($entries->isEmpty()) {
            return ['success' => false, 'message' => 'شرکت‌کننده‌ای وجود ندارد'];
        }

        // محاسبه سهمیه هر استان
        $provinceQuotas = $this->quotaService->calculateQuotasForPeriod($period);

        // محاسبه امتیاز اولویت برای هر شرکت‌کننده
        foreach ($entries as $entry) {
            $score = $this->priorityScoreService->calculate($entry);
            $entry->update(['priority_score' => $score]);
        }

        // گروه‌بندی بر اساس استان
        $entriesByProvince = $entries->groupBy('province_id');

        $winners = collect();
        $waitlist = collect();

        DB::transaction(function () use ($entriesByProvince, $provinceQuotas, &$winners, &$waitlist) {
            foreach ($entriesByProvince as $provinceId => $provinceEntries) {
                $quota = $provinceQuotas[$provinceId] ?? 0;

                // مرتب‌سازی بر اساس امتیاز + عامل تصادفی
                $sorted = $provinceEntries->sortByDesc(function ($entry) {
                    return $entry->priority_score + mt_rand(0, config('welfare.priority_score.random_max', 15));
                });

                $rank = 1;
                foreach ($sorted as $entry) {
                    if ($rank <= $quota) {
                        $entry->update([
                            'status' => LotteryEntry::STATUS_WON,
                            'rank' => $rank,
                        ]);
                        $winners->push($entry);
                    } else {
                        $entry->update([
                            'status' => LotteryEntry::STATUS_WAITLIST,
                            'rank' => $rank,
                        ]);
                        $waitlist->push($entry);
                    }
                    $rank++;
                }
            }
        });

        // به‌روزرسانی وضعیت قرعه‌کشی
        $lottery->update([
            'status' => Lottery::STATUS_DRAWN,
            'drawn_at' => now(),
            'total_participants' => $entries->count(),
            'total_winners' => $winners->count(),
        ]);

        return [
            'success' => true,
            'total_participants' => $entries->count(),
            'total_winners' => $winners->count(),
            'total_waitlist' => $waitlist->count(),
        ];
    }

    public function assignUnits(Lottery $lottery): array
    {
        $period = $lottery->period;
        $center = $period->center;

        $approvedEntries = $lottery->entries()
            ->where('status', LotteryEntry::STATUS_APPROVED)
            ->orderBy('priority_score', 'desc')
            ->get();

        $availableUnits = Unit::where('center_id', $center->id)
            ->where('status', Unit::STATUS_ACTIVE)
            ->whereDoesntHave('reservations', function ($q) use ($period) {
                $q->where('period_id', $period->id)
                    ->whereNotIn('status', ['cancelled', 'no_show']);
            })
            ->orderBy('bed_count')
            ->get();

        $assigned = 0;

        foreach ($approvedEntries as $entry) {
            // یافتن واحد مناسب بر اساس تعداد خانواده
            $familyCount = $entry->family_count;
            $suitableUnit = $this->findSuitableUnit($availableUnits, $familyCount);

            if ($suitableUnit) {
                // ایجاد رزرو
                $entry->reservation()->create([
                    'personnel_id' => $entry->personnel_id,
                    'unit_id' => $suitableUnit->id,
                    'period_id' => $period->id,
                    'guest_count' => $familyCount,
                    'guests' => $entry->guests,
                    'tariff_type' => $this->determineTariffType($entry),
                    'status' => 'confirmed',
                ]);

                // حذف واحد از لیست موجود
                $availableUnits = $availableUnits->reject(fn($u) => $u->id === $suitableUnit->id);
                $assigned++;
            }
        }

        return [
            'success' => true,
            'assigned' => $assigned,
            'remaining_units' => $availableUnits->count(),
        ];
    }

    private function findSuitableUnit(Collection $units, int $familyCount): ?Unit
    {
        // اولویت اول: ظرفیت دقیق
        $exact = $units->first(fn($u) => $u->bed_count === $familyCount);
        if ($exact) return $exact;

        // اولویت دوم: ظرفیت +۱
        $plusOne = $units->first(fn($u) => $u->bed_count === $familyCount + 1);
        if ($plusOne) return $plusOne;

        // اولویت سوم: ظرفیت -۱ (در صورت نیاز می‌توان تشک اضافه گذاشت)
        if ($familyCount > 2) {
            $minusOne = $units->first(fn($u) => $u->bed_count === $familyCount - 1);
            if ($minusOne) return $minusOne;
        }

        // اولویت چهارم: هر واحد با ظرفیت بیشتر
        return $units->first(fn($u) => $u->bed_count >= $familyCount);
    }

    private function determineTariffType(LotteryEntry $entry): string
    {
        $personnel = $entry->personnel;
        $centerId = $entry->lottery->period->center_id;

        if ($personnel->isEligibleForBankRate($centerId)) {
            return 'bank_rate';
        }

        return 'free_bank_rate';
    }

    public function promoteFromWaitlist(LotteryEntry $rejectedEntry): ?LotteryEntry
    {
        // یافتن نفر بعدی از همان استان
        $nextInLine = LotteryEntry::where('lottery_id', $rejectedEntry->lottery_id)
            ->where('province_id', $rejectedEntry->province_id)
            ->where('status', LotteryEntry::STATUS_WAITLIST)
            ->orderBy('rank')
            ->first();

        if ($nextInLine) {
            $nextInLine->update([
                'status' => LotteryEntry::STATUS_WON,
                'rank' => $rejectedEntry->rank,
            ]);

            return $nextInLine;
        }

        return null;
    }
}
