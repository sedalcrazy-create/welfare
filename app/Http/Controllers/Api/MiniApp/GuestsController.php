<?php

namespace App\Http\Controllers\Api\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestsController extends Controller
{
    /**
     * Get all guests for current user's personnel
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        $personnel = $user->personnel;
        $guests = $personnel->guests()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $guests->map(fn($guest) => [
                'id' => $guest->id,
                'national_code' => $guest->national_code,
                'full_name' => $guest->full_name,
                'relation' => $guest->relation,
                'birth_date' => $guest->birth_date,
                'gender' => $guest->gender,
                'phone' => $guest->phone,
                'notes' => $guest->pivot->notes ?? null,
            ])
        ]);
    }

    /**
     * Store a new guest
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        $validated = $request->validate([
            'national_code' => 'required|string|size:10',
            'full_name' => 'required|string|max:255',
            'relation' => 'required|string|max:100',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $personnel = $user->personnel;

            // Check if guest already exists
            $guest = Guest::where('national_code', $validated['national_code'])->first();

            if (!$guest) {
                // Create new guest
                $guest = Guest::create([
                    'national_code' => $validated['national_code'],
                    'full_name' => $validated['full_name'],
                    'relation' => $validated['relation'],
                    'birth_date' => $validated['birth_date'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                ]);
            }

            // Check if already attached
            if ($personnel->guests()->where('guest_id', $guest->id)->exists()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'این مهمان قبلاً اضافه شده است.'
                ], 400);
            }

            // Attach guest to personnel
            $personnel->guests()->attach($guest->id, [
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            \Log::info('Guest added from Bale Mini App', [
                'user_id' => $user->id,
                'personnel_id' => $personnel->id,
                'guest_id' => $guest->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'مهمان با موفقیت اضافه شد.',
                'data' => [
                    'id' => $guest->id,
                    'national_code' => $guest->national_code,
                    'full_name' => $guest->full_name,
                    'relation' => $guest->relation,
                    'birth_date' => $guest->birth_date,
                    'gender' => $guest->gender,
                    'phone' => $guest->phone,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to add guest from Bale Mini App', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در افزودن مهمان. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    /**
     * Show guest details
     *
     * @param Request $request
     * @param Guest $guest
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Guest $guest)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        // Check if guest belongs to this personnel
        $personnel = $user->personnel;
        if (!$personnel->guests()->where('guest_id', $guest->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی غیرمجاز.'
            ], 403);
        }

        $pivot = $personnel->guests()->where('guest_id', $guest->id)->first()->pivot;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $guest->id,
                'national_code' => $guest->national_code,
                'full_name' => $guest->full_name,
                'relation' => $guest->relation,
                'birth_date' => $guest->birth_date,
                'gender' => $guest->gender,
                'phone' => $guest->phone,
                'notes' => $pivot->notes ?? null,
            ]
        ]);
    }

    /**
     * Update guest
     *
     * @param Request $request
     * @param Guest $guest
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Guest $guest)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        // Check if guest belongs to this personnel
        $personnel = $user->personnel;
        if (!$personnel->guests()->where('guest_id', $guest->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی غیرمجاز.'
            ], 403);
        }

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'relation' => 'sometimes|string|max:100',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Update guest info
            $guestData = array_intersect_key($validated, array_flip(['full_name', 'relation', 'birth_date', 'gender', 'phone']));
            if (!empty($guestData)) {
                $guest->update($guestData);
            }

            // Update pivot notes
            if (isset($validated['notes'])) {
                $personnel->guests()->updateExistingPivot($guest->id, [
                    'notes' => $validated['notes']
                ]);
            }

            DB::commit();

            \Log::info('Guest updated from Bale Mini App', [
                'user_id' => $user->id,
                'guest_id' => $guest->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'مهمان با موفقیت بروزرسانی شد.',
                'data' => [
                    'id' => $guest->id,
                    'national_code' => $guest->national_code,
                    'full_name' => $guest->full_name,
                    'relation' => $guest->relation,
                    'birth_date' => $guest->birth_date,
                    'gender' => $guest->gender,
                    'phone' => $guest->phone,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to update guest from Bale Mini App', [
                'user_id' => $user->id,
                'guest_id' => $guest->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی مهمان. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    /**
     * Delete guest
     *
     * @param Request $request
     * @param Guest $guest
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Guest $guest)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        // Check if guest belongs to this personnel
        $personnel = $user->personnel;
        if (!$personnel->guests()->where('guest_id', $guest->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی غیرمجاز.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Detach guest from personnel
            $personnel->guests()->detach($guest->id);

            // If guest has no other personnel, delete guest
            if ($guest->personnel()->count() === 0) {
                $guest->delete();
            }

            DB::commit();

            \Log::info('Guest removed from Bale Mini App', [
                'user_id' => $user->id,
                'guest_id' => $guest->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'مهمان با موفقیت حذف شد.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to remove guest from Bale Mini App', [
                'user_id' => $user->id,
                'guest_id' => $guest->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف مهمان. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    /**
     * Search personnel to add as guest
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchPersonnel(Request $request)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        $request->validate([
            'employee_code' => 'required|string'
        ]);

        $personnel = Personnel::where('employee_code', $request->employee_code)
            ->where('status', 'approved')
            ->where('id', '!=', $user->personnel->id) // Cannot add self
            ->first(['id', 'employee_code', 'national_code', 'full_name', 'province_id']);

        if (!$personnel) {
            return response()->json([
                'success' => false,
                'message' => 'پرسنلی با این کد پرسنلی یافت نشد.'
            ], 404);
        }

        // Check if already added
        if ($user->personnel->personnelGuests()->where('guest_personnel_id', $personnel->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این پرسنل قبلاً به عنوان همراه اضافه شده است.'
            ], 400);
        }

        $personnel->load('province:id,name');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $personnel->id,
                'employee_code' => $personnel->employee_code,
                'national_code' => $personnel->national_code,
                'full_name' => $personnel->full_name,
                'province' => [
                    'id' => $personnel->province->id,
                    'name' => $personnel->province->name,
                ],
            ]
        ]);
    }

    /**
     * Add personnel as guest
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPersonnelGuest(Request $request)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        $validated = $request->validate([
            'personnel_id' => 'required|exists:personnel,id',
            'relation' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $guestPersonnel = Personnel::findOrFail($validated['personnel_id']);

        if ($guestPersonnel->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'این پرسنل هنوز تأیید نشده است.'
            ], 400);
        }

        if ($guestPersonnel->id === $user->personnel->id) {
            return response()->json([
                'success' => false,
                'message' => 'نمی‌توانید خودتان را به عنوان همراه اضافه کنید.'
            ], 400);
        }

        $personnel = $user->personnel;

        // Check if already added
        if ($personnel->personnelGuests()->where('guest_personnel_id', $guestPersonnel->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این پرسنل قبلاً به عنوان همراه اضافه شده است.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Attach personnel as guest
            $personnel->personnelGuests()->attach($guestPersonnel->id, [
                'relation' => $validated['relation'],
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            \Log::info('Personnel guest added from Bale Mini App', [
                'user_id' => $user->id,
                'personnel_id' => $personnel->id,
                'guest_personnel_id' => $guestPersonnel->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'پرسنل با موفقیت به عنوان همراه اضافه شد.',
                'data' => [
                    'id' => $guestPersonnel->id,
                    'employee_code' => $guestPersonnel->employee_code,
                    'national_code' => $guestPersonnel->national_code,
                    'full_name' => $guestPersonnel->full_name,
                    'relation' => $validated['relation'],
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to add personnel guest from Bale Mini App', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در افزودن همراه. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    /**
     * Get personnel guests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function personnelGuests(Request $request)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        $personnel = $user->personnel;
        $personnelGuests = $personnel->personnelGuests()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $personnelGuests->map(fn($guest) => [
                'id' => $guest->id,
                'employee_code' => $guest->employee_code,
                'national_code' => $guest->national_code,
                'full_name' => $guest->full_name,
                'relation' => $guest->pivot->relation,
                'notes' => $guest->pivot->notes ?? null,
            ])
        ]);
    }

    /**
     * Remove personnel guest
     *
     * @param Request $request
     * @param Personnel $personnelGuest
     * @return \Illuminate\Http\JsonResponse
     */
    public function removePersonnelGuest(Request $request, Personnel $personnelGuest)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        $personnel = $user->personnel;

        if (!$personnel->personnelGuests()->where('guest_personnel_id', $personnelGuest->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی غیرمجاز.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $personnel->personnelGuests()->detach($personnelGuest->id);

            DB::commit();

            \Log::info('Personnel guest removed from Bale Mini App', [
                'user_id' => $user->id,
                'personnel_id' => $personnel->id,
                'guest_personnel_id' => $personnelGuest->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'همراه با موفقیت حذف شد.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to remove personnel guest from Bale Mini App', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف همراه. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }
}
