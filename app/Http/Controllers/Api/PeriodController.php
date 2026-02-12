<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Morilog\Jalali\Jalalian;

class PeriodController extends Controller
{
    /**
     * Get list of periods (filtered by center and status)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'center_id' => 'nullable|exists:centers,id',
            'status' => 'nullable|in:draft,open,closed',
        ]);

        $query = Period::with('center:id,name,slug,city,type,stay_duration')
            ->select('id', 'center_id', 'title', 'start_date', 'end_date', 'capacity', 'status', 'season_type');

        // Filter by center
        if ($request->filled('center_id')) {
            $query->where('center_id', $request->center_id);
        }

        // Filter by status (default: open)
        $status = $request->get('status', 'open');
        $query->where('status', $status);

        // Only show future or current periods
        $query->where('end_date', '>=', now());

        $periods = $query->orderBy('start_date')
            ->get()
            ->map(function ($period) {
                return [
                    'id' => $period->id,
                    'center_id' => $period->center_id,
                    'center_name' => $period->center->name,
                    'center_city' => $period->center->city,
                    'title' => $period->title,
                    'start_date' => $period->start_date_jalali,
                    'end_date' => $period->end_date_jalali,
                    'start_date_gregorian' => $period->start_date->format('Y-m-d'),
                    'end_date_gregorian' => $period->end_date->format('Y-m-d'),
                    'duration_days' => $period->duration_days,
                    'capacity' => $period->capacity,
                    'registered_count' => $period->getRegisteredCount(),
                    'remaining_capacity' => $period->getRemainingCapacity(),
                    'is_available' => $period->isAvailable(),
                    'status' => $period->status,
                    'status_label' => $this->getPeriodStatusLabel($period->status),
                    'season_type' => $period->season_type,
                    'season_label' => $this->getSeasonTypeLabel($period->season_type),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $periods,
            'total' => $periods->count()
        ]);
    }

    /**
     * Get period details by ID
     *
     * @param Period $period
     * @return JsonResponse
     */
    public function show(Period $period): JsonResponse
    {
        $period->load('center:id,name,slug,city,type,stay_duration,address,phone');

        $data = [
            'id' => $period->id,
            'center' => [
                'id' => $period->center->id,
                'name' => $period->center->name,
                'city' => $period->center->city,
                'type' => $period->center->type,
                'stay_duration' => $period->center->stay_duration,
            ],
            'title' => $period->title,
            'description' => $period->description,
            'start_date' => $period->start_date_jalali,
            'end_date' => $period->end_date_jalali,
            'start_date_gregorian' => $period->start_date->format('Y-m-d'),
            'end_date_gregorian' => $period->end_date->format('Y-m-d'),
            'duration_days' => $period->duration_days,
            'capacity' => $period->capacity,
            'registered_count' => $period->getRegisteredCount(),
            'remaining_capacity' => $period->getRemainingCapacity(),
            'is_available' => $period->isAvailable(),
            'status' => $period->status,
            'status_label' => $this->getPeriodStatusLabel($period->status),
            'season_type' => $period->season_type,
            'season_label' => $this->getSeasonTypeLabel($period->season_type),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get available periods for a specific center
     *
     * @param Center $center
     * @return JsonResponse
     */
    public function byCenter(Center $center): JsonResponse
    {
        $periods = Period::where('center_id', $center->id)
            ->where('status', 'open')
            ->where('end_date', '>=', now())
            ->orderBy('start_date')
            ->get()
            ->map(function ($period) {
                return [
                    'id' => $period->id,
                    'title' => $period->title,
                    'start_date' => $period->start_date_jalali,
                    'end_date' => $period->end_date_jalali,
                    'duration_days' => $period->duration_days,
                    'capacity' => $period->capacity,
                    'remaining_capacity' => $period->getRemainingCapacity(),
                    'is_available' => $period->isAvailable(),
                    'season_type' => $period->season_type,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $periods,
            'center' => [
                'id' => $center->id,
                'name' => $center->name,
            ],
            'total' => $periods->count()
        ]);
    }

    /**
     * Get period status label in Persian
     *
     * @param string $status
     * @return string
     */
    private function getPeriodStatusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'پیش‌نویس',
            'open' => 'باز',
            'closed' => 'بسته شده',
            default => 'نامشخص'
        };
    }

    /**
     * Get season type label in Persian
     *
     * @param string|null $seasonType
     * @return string
     */
    private function getSeasonTypeLabel(?string $seasonType): string
    {
        return match ($seasonType) {
            'golden_peak' => 'اوج طلایی',
            'peak' => 'اوج',
            'mid_season' => 'میان فصل',
            'off_peak' => 'کم‌بار',
            'super_off_peak' => 'خیلی کم‌بار',
            default => 'نامشخص'
        };
    }
}
