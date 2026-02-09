<?php

namespace App\Services;

use App\Models\Period;
use App\Models\Province;
use Illuminate\Support\Collection;

class QuotaService
{
    public function calculateQuotasForPeriod(Period $period): array
    {
        $totalCapacity = $period->capacity;
        $provinces = Province::where('is_active', true)->get();

        $quotas = [];
        $totalAssigned = 0;

        foreach ($provinces as $province) {
            $quota = (int) round($totalCapacity * $province->quota_ratio);
            $quotas[$province->id] = $quota;
            $totalAssigned += $quota;
        }

        // تنظیم اختلاف ناشی از گرد کردن
        $diff = $totalCapacity - $totalAssigned;
        if ($diff !== 0) {
            // افزودن/کاهش از بزرگترین استان‌ها
            $sortedProvinces = $provinces->sortByDesc('personnel_count');
            $i = 0;
            while ($diff !== 0) {
                $provinceId = $sortedProvinces->values()[$i % $sortedProvinces->count()]->id;
                if ($diff > 0) {
                    $quotas[$provinceId]++;
                    $diff--;
                } else {
                    if ($quotas[$provinceId] > 0) {
                        $quotas[$provinceId]--;
                        $diff++;
                    }
                }
                $i++;
            }
        }

        return $quotas;
    }

    public function getQuotaSummary(Period $period): Collection
    {
        $quotas = $this->calculateQuotasForPeriod($period);
        $provinces = Province::whereIn('id', array_keys($quotas))->get()->keyBy('id');

        return collect($quotas)->map(function ($quota, $provinceId) use ($provinces) {
            $province = $provinces[$provinceId];
            return [
                'province_id' => $provinceId,
                'province_name' => $province->name,
                'province_code' => $province->code,
                'personnel_count' => $province->personnel_count,
                'quota' => $quota,
            ];
        })->sortByDesc('quota')->values();
    }

    public function recalculateProvinceRatios(): void
    {
        $totalPersonnel = Province::where('is_active', true)->sum('personnel_count');

        Province::where('is_active', true)->each(function ($province) use ($totalPersonnel) {
            $province->update([
                'quota_ratio' => $totalPersonnel > 0
                    ? $province->personnel_count / $totalPersonnel
                    : 0,
            ]);
        });
    }

    public function updateProvincePersonnelCount(int $provinceId, int $count): void
    {
        Province::where('id', $provinceId)->update(['personnel_count' => $count]);
        $this->recalculateProvinceRatios();
    }
}
