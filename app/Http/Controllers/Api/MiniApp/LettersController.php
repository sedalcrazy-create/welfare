<?php

namespace App\Http\Controllers\Api\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\IntroductionLetter;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LettersController extends Controller
{
    /**
     * Get all introduction letters for current user
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

        $query = IntroductionLetter::where('personnel_id', $user->personnel->id)
            ->with(['center:id,name,slug', 'issuedByUser:id,name']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $letters = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $letters->map(fn($letter) => [
                'id' => $letter->id,
                'letter_code' => $letter->letter_code,
                'center' => [
                    'id' => $letter->center->id,
                    'name' => $letter->center->name,
                    'slug' => $letter->center->slug,
                ],
                'status' => $letter->status,
                'guests_count' => is_array($letter->guests) ? count($letter->guests) : 0,
                'tariff_type' => $letter->tariff_type,
                'issue_date' => $letter->issue_date,
                'expiry_date' => $letter->expiry_date,
                'issued_by' => $letter->issuedByUser->name ?? null,
                'created_at' => $letter->created_at->toISOString(),
            ])
        ]);
    }

    /**
     * Show letter details
     *
     * @param Request $request
     * @param IntroductionLetter $letter
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, IntroductionLetter $letter)
    {
        $user = $request->user();

        // Check if letter belongs to this user's personnel
        if ($letter->personnel_id !== $user->personnel?->id) {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی غیرمجاز.'
            ], 403);
        }

        $letter->load(['center:id,name,slug,city', 'issuedByUser:id,name']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $letter->id,
                'letter_code' => $letter->letter_code,
                'center' => [
                    'id' => $letter->center->id,
                    'name' => $letter->center->name,
                    'slug' => $letter->center->slug,
                    'city' => $letter->center->city,
                ],
                'status' => $letter->status,
                'guests' => $letter->guests ?? [],
                'tariff_type' => $letter->tariff_type,
                'issue_date' => $letter->issue_date,
                'expiry_date' => $letter->expiry_date,
                'issued_by' => $letter->issuedByUser->name ?? null,
                'notes' => $letter->notes,
                'qr_code' => $letter->qr_code,
                'created_at' => $letter->created_at->toISOString(),
                'updated_at' => $letter->updated_at->toISOString(),
            ]
        ]);
    }

    /**
     * Create new introduction letter request
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

        if ($user->personnel->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'حساب کاربری شما هنوز تأیید نشده است.'
            ], 403);
        }

        $validated = $request->validate([
            'center_id' => 'required|exists:centers,id',
            'guests' => 'required|array|min:1',
            'guests.*.type' => 'required|in:self,guest,personnel',
            'guests.*.guest_id' => 'required_if:guests.*.type,guest|exists:guests,id',
            'guests.*.personnel_id' => 'required_if:guests.*.type,personnel|exists:personnel,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $center = Center::findOrFail($validated['center_id']);

        if (!$center->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'این مرکز در حال حاضر غیرفعال است.'
            ], 400);
        }

        $personnel = $user->personnel;

        // Check quota for this center
        $quota = $user->centerQuotas()->where('center_id', $center->id)->first();

        if (!$quota || $quota->getRemainingQuota() <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'سهمیه شما برای این مرکز به پایان رسیده است.'
            ], 400);
        }

        // Build guests array
        $guests = [];
        foreach ($validated['guests'] as $guestData) {
            if ($guestData['type'] === 'self') {
                $guests[] = [
                    'type' => 'self',
                    'name' => $personnel->full_name,
                    'national_code' => $personnel->national_code,
                ];
            } elseif ($guestData['type'] === 'guest') {
                $guest = $personnel->guests()->find($guestData['guest_id']);
                if (!$guest) {
                    return response()->json([
                        'success' => false,
                        'message' => 'یکی از مهمانان انتخاب شده نامعتبر است.'
                    ], 400);
                }
                $guests[] = [
                    'type' => 'guest',
                    'name' => $guest->full_name,
                    'national_code' => $guest->national_code,
                    'relation' => $guest->relation,
                ];
            } elseif ($guestData['type'] === 'personnel') {
                $guestPersonnel = $personnel->personnelGuests()->find($guestData['personnel_id']);
                if (!$guestPersonnel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'یکی از همراهان انتخاب شده نامعتبر است.'
                    ], 400);
                }
                $guests[] = [
                    'type' => 'personnel',
                    'name' => $guestPersonnel->full_name,
                    'national_code' => $guestPersonnel->national_code,
                    'employee_code' => $guestPersonnel->employee_code,
                ];
            }
        }

        DB::beginTransaction();

        try {
            // Generate unique letter code
            $letterCode = 'L-' . date('Y') . '-' . str_pad(
                IntroductionLetter::whereYear('created_at', date('Y'))->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            // Create introduction letter
            $letter = IntroductionLetter::create([
                'letter_code' => $letterCode,
                'personnel_id' => $personnel->id,
                'center_id' => $center->id,
                'issued_by_user_id' => $user->id,
                'assigned_user_id' => $user->id,
                'status' => 'active',
                'guests' => $guests,
                'tariff_type' => 'bank_rate', // Default
                'issue_date' => now()->format('Y-m-d'),
                'expiry_date' => now()->addMonths(3)->format('Y-m-d'),
                'notes' => $validated['notes'] ?? null,
                'qr_code' => base64_encode($letterCode),
            ]);

            // Decrease quota
            $quota->increment('used_quota');

            DB::commit();

            \Log::info('Introduction letter created from Bale Mini App', [
                'user_id' => $user->id,
                'letter_id' => $letter->id,
                'center_id' => $center->id,
            ]);

            $letter->load('center:id,name,slug');

            return response()->json([
                'success' => true,
                'message' => 'معرفی‌نامه با موفقیت صادر شد.',
                'data' => [
                    'id' => $letter->id,
                    'letter_code' => $letter->letter_code,
                    'center' => [
                        'id' => $letter->center->id,
                        'name' => $letter->center->name,
                    ],
                    'status' => $letter->status,
                    'guests' => $letter->guests,
                    'issue_date' => $letter->issue_date,
                    'expiry_date' => $letter->expiry_date,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to create introduction letter from Bale Mini App', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در صدور معرفی‌نامه. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    /**
     * Cancel introduction letter
     *
     * @param Request $request
     * @param IntroductionLetter $letter
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, IntroductionLetter $letter)
    {
        $user = $request->user();

        // Check if letter belongs to this user's personnel
        if ($letter->personnel_id !== $user->personnel?->id) {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی غیرمجاز.'
            ], 403);
        }

        if ($letter->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'این معرفی‌نامه قبلاً لغو شده است.'
            ], 400);
        }

        if ($letter->status === 'used') {
            return response()->json([
                'success' => false,
                'message' => 'این معرفی‌نامه استفاده شده است و قابل لغو نیست.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Update status to cancelled
            $letter->update([
                'status' => 'cancelled'
            ]);

            // Restore quota
            $quota = $user->centerQuotas()->where('center_id', $letter->center_id)->first();
            if ($quota) {
                $quota->decrement('used_quota');
            }

            DB::commit();

            \Log::info('Introduction letter cancelled from Bale Mini App', [
                'user_id' => $user->id,
                'letter_id' => $letter->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'معرفی‌نامه با موفقیت لغو شد.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to cancel introduction letter from Bale Mini App', [
                'user_id' => $user->id,
                'letter_id' => $letter->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در لغو معرفی‌نامه. لطفاً دوباره تلاش کنید.'
            ], 500);
        }
    }

    /**
     * Get available centers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function centers()
    {
        $centers = Center::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'city', 'type', 'stay_duration', 'bed_count', 'unit_count']);

        return response()->json([
            'success' => true,
            'data' => $centers
        ]);
    }

    /**
     * Get user's quotas per center
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quotas(Request $request)
    {
        $user = $request->user();

        if (!$user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.'
            ], 404);
        }

        $quotas = $user->centerQuotas()->with('center:id,name,slug')->get();

        return response()->json([
            'success' => true,
            'data' => $quotas->mapWithKeys(fn($quota) => [
                $quota->center->slug => [
                    'center_id' => $quota->center_id,
                    'center_name' => $quota->center->name,
                    'total' => $quota->total_quota,
                    'used' => $quota->used_quota,
                    'remaining' => $quota->getRemainingQuota(),
                ]
            ])
        ]);
    }
}
