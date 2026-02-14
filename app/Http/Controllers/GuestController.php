<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class GuestController extends Controller
{
    /**
     * نمایش لیست مهمانان یک پرسنل (Ajax)
     */
    public function index(Personnel $personnel)
    {
        $guests = $personnel->guests()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'guests' => $guests->map(function ($guest) {
                return [
                    'id' => $guest->id,
                    'national_code' => $guest->national_code,
                    'full_name' => $guest->full_name,
                    'relation' => $guest->relation,
                    'birth_date' => $guest->birth_date?->format('Y/m/d'),
                    'gender' => $guest->gender,
                    'phone' => $guest->phone,
                    'is_bank_affiliated' => $guest->isBankAffiliated(),
                    'badge_class' => $guest->getRelationBadgeClass(),
                    'badge_text' => $guest->getRelationBadgeText(),
                    'pivot_notes' => $guest->pivot->notes,
                ];
            }),
        ]);
    }

    /**
     * افزودن مهمان جدید یا اتصال مهمان موجود به پرسنل
     */
    public function store(Request $request, Personnel $personnel)
    {
        $validated = $request->validate([
            'national_code' => 'required|string|size:10',
            'full_name' => 'required|string|max:255',
            'relation' => 'required|string|max:50',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        // جستجوی مهمان با کد ملی
        $guest = Guest::findByNationalCode($validated['national_code']);

        if (!$guest) {
            // مهمان جدید
            $guest = Guest::create([
                'national_code' => $validated['national_code'],
                'full_name' => $validated['full_name'],
                'relation' => $validated['relation'],
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);
        }

        // بررسی اینکه مهمان قبلاً به این پرسنل متصل شده یا نه
        if ($personnel->guests()->where('guest_id', $guest->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این مهمان قبلاً به لیست اضافه شده است.',
            ], 422);
        }

        // اتصال به پرسنل
        $personnel->guests()->attach($guest->id, [
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'مهمان با موفقیت اضافه شد.',
            'guest' => [
                'id' => $guest->id,
                'national_code' => $guest->national_code,
                'full_name' => $guest->full_name,
                'relation' => $guest->relation,
                'birth_date' => $guest->birth_date?->format('Y/m/d'),
                'gender' => $guest->gender,
                'phone' => $guest->phone,
                'is_bank_affiliated' => $guest->isBankAffiliated(),
                'badge_class' => $guest->getRelationBadgeClass(),
                'badge_text' => $guest->getRelationBadgeText(),
            ],
        ]);
    }

    /**
     * نمایش اطلاعات یک مهمان
     */
    public function show(Personnel $personnel, Guest $guest)
    {
        // بررسی اینکه این مهمان متعلق به این پرسنل است
        if (!$personnel->guests()->where('guest_id', $guest->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'مهمان یافت نشد.',
            ], 404);
        }

        $pivotData = $personnel->guests()->where('guest_id', $guest->id)->first()->pivot;

        return response()->json([
            'guest' => [
                'id' => $guest->id,
                'national_code' => $guest->national_code,
                'full_name' => $guest->full_name,
                'relation' => $guest->relation,
                'birth_date' => $guest->birth_date?->format('Y/m/d'),
                'gender' => $guest->gender,
                'phone' => $guest->phone,
                'is_bank_affiliated' => $guest->isBankAffiliated(),
                'badge_class' => $guest->getRelationBadgeClass(),
                'badge_text' => $guest->getRelationBadgeText(),
                'pivot_notes' => $pivotData->notes,
            ],
        ]);
    }

    /**
     * ویرایش یادداشت مهمان در لیست پرسنل
     */
    public function update(Request $request, Personnel $personnel, Guest $guest)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        // بررسی اینکه این مهمان متعلق به این پرسنل است
        if (!$personnel->guests()->where('guest_id', $guest->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'مهمان یافت نشد.',
            ], 404);
        }

        // به‌روزرسانی pivot
        $personnel->guests()->updateExistingPivot($guest->id, [
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'یادداشت به‌روزرسانی شد.',
        ]);
    }

    /**
     * حذف مهمان از لیست پرسنل (detach)
     */
    public function destroy(Personnel $personnel, Guest $guest)
    {
        // بررسی اینکه این مهمان متعلق به این پرسنل است
        if (!$personnel->guests()->where('guest_id', $guest->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'مهمان یافت نشد.',
            ], 404);
        }

        // حذف از لیست (detach)
        $personnel->guests()->detach($guest->id);

        return response()->json([
            'success' => true,
            'message' => 'مهمان از لیست حذف شد.',
        ]);
    }
}
