<?php

namespace App\Services;

use App\Models\LotteryEntry;
use App\Models\Personnel;
use App\Models\UsageHistory;

class PriorityScoreService
{
    public function calculate(LotteryEntry $entry): float
    {
        $personnel = $entry->personnel;
        $centerId = $entry->lottery->period->center_id;
        $config = config('welfare.priority_score');

        $score = $config['base_score'];

        // امتیاز سابقه عدم استفاده
        $daysSinceLastUse = $personnel->getDaysSinceLastUsage($centerId);
        if ($daysSinceLastUse === null) {
            // هرگز استفاده نکرده
            $score += $config['never_used_bonus'];
        } else {
            $score += $daysSinceLastUse * $config['days_since_last_use_multiplier'];
        }

        // امتیاز سابقه خدمت
        $score += $personnel->service_years * $config['service_years_multiplier'];

        // کاهش امتیاز برای استفاده مکرر در سال جاری
        $usageThisYear = $this->getUsageCountThisYear($personnel);
        $score -= $usageThisYear * $config['usage_penalty_multiplier'];

        // امتیاز تطابق ظرفیت
        $score += $this->calculateFamilyMatchBonus($entry, $config['family_match_bonus']);

        // امتیاز ایثارگران
        if ($personnel->is_isargar) {
            $score += $config['isargar_bonus'];
        }

        return max(0, $score);
    }

    private function getUsageCountThisYear(Personnel $personnel): int
    {
        $jalaliYear = jdate()->getYear();

        return UsageHistory::where('personnel_id', $personnel->id)
            ->where('jalali_year', $jalaliYear)
            ->count();
    }

    private function calculateFamilyMatchBonus(LotteryEntry $entry, float $baseBonus): float
    {
        $familyCount = $entry->family_count;

        // خانواده‌های کوچک‌تر اولویت بیشتر (کمتر هدررفت ظرفیت)
        if ($familyCount <= 3) {
            return $baseBonus * 1.5;
        } elseif ($familyCount <= 5) {
            return $baseBonus;
        } else {
            return $baseBonus * 0.5;
        }
    }

    public function getScoreBreakdown(LotteryEntry $entry): array
    {
        $personnel = $entry->personnel;
        $centerId = $entry->lottery->period->center_id;
        $config = config('welfare.priority_score');

        $daysSinceLastUse = $personnel->getDaysSinceLastUsage($centerId);
        $usageThisYear = $this->getUsageCountThisYear($personnel);

        return [
            'base_score' => $config['base_score'],
            'days_since_last_use' => $daysSinceLastUse,
            'days_bonus' => $daysSinceLastUse
                ? $daysSinceLastUse * $config['days_since_last_use_multiplier']
                : $config['never_used_bonus'],
            'service_years' => $personnel->service_years,
            'service_bonus' => $personnel->service_years * $config['service_years_multiplier'],
            'usage_this_year' => $usageThisYear,
            'usage_penalty' => $usageThisYear * $config['usage_penalty_multiplier'],
            'family_count' => $entry->family_count,
            'family_bonus' => $this->calculateFamilyMatchBonus($entry, $config['family_match_bonus']),
            'is_isargar' => $personnel->is_isargar,
            'isargar_bonus' => $personnel->is_isargar ? $config['isargar_bonus'] : 0,
            'total' => $this->calculate($entry),
        ];
    }
}
