<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Center;
use Illuminate\Http\JsonResponse;

class CenterController extends Controller
{
    /**
     * Get list of active centers for Bale bot
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $centers = Center::where('is_active', true)
            ->select('id', 'name', 'slug', 'city', 'type', 'stay_duration', 'total_units', 'total_beds')
            ->orderBy('name')
            ->get()
            ->map(function ($center) {
                return [
                    'id' => $center->id,
                    'name' => $center->name,
                    'slug' => $center->slug,
                    'city' => $center->city,
                    'type' => $center->type,
                    'type_label' => $this->getCenterTypeLabel($center->type),
                    'stay_duration' => $center->stay_duration,
                    'total_units' => $center->total_units,
                    'total_beds' => $center->total_beds,
                    'description' => $this->getCenterDescription($center),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $centers,
            'total' => $centers->count()
        ]);
    }

    /**
     * Get center details by ID
     *
     * @param Center $center
     * @return JsonResponse
     */
    public function show(Center $center): JsonResponse
    {
        if (!$center->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'این مرکز در حال حاضر غیرفعال است'
            ], 404);
        }

        $data = [
            'id' => $center->id,
            'name' => $center->name,
            'slug' => $center->slug,
            'city' => $center->city,
            'type' => $center->type,
            'type_label' => $this->getCenterTypeLabel($center->type),
            'stay_duration' => $center->stay_duration,
            'total_units' => $center->total_units,
            'total_beds' => $center->total_beds,
            'address' => $center->address,
            'phone' => $center->phone,
            'description' => $center->description,
            'amenities' => $center->amenities,
            'check_in_time' => $center->check_in_time,
            'check_out_time' => $center->check_out_time,
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get center type label in Persian
     *
     * @param string $type
     * @return string
     */
    private function getCenterTypeLabel(string $type): string
    {
        return match ($type) {
            'religious' => 'زیارتی',
            'beach' => 'ساحلی',
            'mountain' => 'کوهستانی',
            default => 'نامشخص'
        };
    }

    /**
     * Get center description for Bale bot
     *
     * @param Center $center
     * @return string
     */
    private function getCenterDescription(Center $center): string
    {
        $typeLabel = $this->getCenterTypeLabel($center->type);
        $duration = $center->stay_duration;

        return "{$center->name} - {$typeLabel} - {$center->city} ({$duration} شب)";
    }
}
