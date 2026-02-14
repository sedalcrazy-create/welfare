<?php

namespace App\Http\Controllers\Api\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonnelController extends Controller
{
    /**
     * Get current user's personnel info
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.',
                'data' => null
            ], 404);
        }

        $personnel = $user->personnel;
        $personnel->load(['province', 'guests', 'personnelGuests']);

        // Get quota information per center
        $quotas = $user->centerQuotas()->with('center')->get()->mapWithKeys(function ($quota) {
            return [$quota->center->slug => [
                'center_id' => $quota->center_id,
                'center_name' => $quota->center->name,
                'total' => $quota->total_quota,
                'used' => $quota->used_quota,
                'remaining' => $quota->getRemainingQuota(),
            ]];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $personnel->id,
                'employee_code' => $personnel->employee_code,
                'national_code' => $personnel->national_code,
                'full_name' => $personnel->full_name,
                'mobile' => $personnel->mobile,
                'province' => [
                    'id' => $personnel->province->id,
                    'name' => $personnel->province->name,
                ],
                'status' => $personnel->status,
                'is_isargar' => $personnel->is_isargar,
                'service_years' => $personnel->service_years,
                'family_members' => $personnel->family_members,
                'family_members_count' => is_array($personnel->family_members) ? count($personnel->family_members) : 0,
                'guests_count' => $personnel->guests->count(),
                'personnel_guests_count' => $personnel->personnelGuests->count(),
                'quotas' => $quotas,
            ]
        ]);
    }

    /**
     * Register new personnel for current user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $user = $request->user();

        // Check if user already has personnel
        if ($user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'شما قبلاً ثبت‌نام کرده‌اید.'
            ], 400);
        }

        $validated = $request->validate([
            'employee_code' => 'required|string|max:20|unique:personnel,employee_code',
            'national_code' => 'required|string|size:10|unique:personnel,national_code',
            'full_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'province_id' => 'required|exists:provinces,id',
            'is_isargar' => 'boolean',
            'service_years' => 'nullable|integer|min:0|max:50',
            'family_members' => 'nullable|array',
            'family_members.*.name' => 'required|string|max:255',
            'family_members.*.relation' => 'required|string|max:50',
            'family_members.*.national_code' => 'nullable|string|size:10',
        ]);

        DB::beginTransaction();

        try {
            // Create personnel
            $personnel = Personnel::create([
                'employee_code' => $validated['employee_code'],
                'national_code' => $validated['national_code'],
                'full_name' => $validated['full_name'],
                'mobile' => $validated['mobile'],
                'province_id' => $validated['province_id'],
                'is_isargar' => $validated['is_isargar'] ?? false,
                'service_years' => $validated['service_years'] ?? 0,
                'family_members' => $validated['family_members'] ?? [],
                'status' => 'pending', // Needs admin approval
            ]);

            // Link personnel to user
            $user->update([
                'personnel_id' => $personnel->id,
                'province_id' => $validated['province_id'],
            ]);

            DB::commit();

            $personnel->load('province');

            \Log::info('Personnel registered from Bale Mini App', [
                'user_id' => $user->id,
                'personnel_id' => $personnel->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ثبت‌نام شما با موفقیت انجام شد. پس از تأیید مدیر استانی می‌توانید از سیستم استفاده کنید.',
                'data' => [
                    'id' => $personnel->id,
                    'employee_code' => $personnel->employee_code,
                    'full_name' => $personnel->full_name,
                    'status' => $personnel->status,
                    'province' => [
                        'id' => $personnel->province->id,
                        'name' => $personnel->province->name,
                    ],
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Personnel registration failed from Bale Mini App', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    /**
     * Update personnel information
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        $validated = $request->validate([
            'mobile' => 'sometimes|string|max:20',
            'family_members' => 'sometimes|array',
            'family_members.*.name' => 'required|string|max:255',
            'family_members.*.relation' => 'required|string|max:50',
            'family_members.*.national_code' => 'nullable|string|size:10',
        ]);

        $personnel = $user->personnel;

        // Only allow updating certain fields
        $allowedFields = ['mobile', 'family_members'];
        $updateData = array_intersect_key($validated, array_flip($allowedFields));

        $personnel->update($updateData);

        \Log::info('Personnel updated from Bale Mini App', [
            'user_id' => $user->id,
            'personnel_id' => $personnel->id,
            'updated_fields' => array_keys($updateData),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'اطلاعات با موفقیت بروزرسانی شد.',
            'data' => [
                'id' => $personnel->id,
                'mobile' => $personnel->mobile,
                'family_members' => $personnel->family_members,
            ]
        ]);
    }

    /**
     * Get all provinces
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function provinces()
    {
        $provinces = Province::orderBy('name')->get(['id', 'name', 'code']);

        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
    }
}
