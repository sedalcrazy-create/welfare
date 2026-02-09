<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PersonnelRequestController extends Controller
{
    /**
     * Register a new personnel request from Bale bot
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'national_code' => 'required|string|size:10|unique:personnel,national_code',
            'phone' => 'required|string|max:20',
            'family_count' => 'required|integer|min:1|max:10',
            'preferred_center_id' => 'required|exists:centers,id',
            'bale_user_id' => 'nullable|string|unique:personnel,bale_user_id',
        ], [
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است',
            'preferred_center_id.exists' => 'مرکز انتخاب شده معتبر نیست',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی اطلاعات',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create personnel request
        $personnel = Personnel::create([
            'full_name' => $request->full_name,
            'national_code' => $request->national_code,
            'phone' => $request->phone,
            'family_count' => $request->family_count,
            'preferred_center_id' => $request->preferred_center_id,
            'bale_user_id' => $request->bale_user_id,
            'registration_source' => Personnel::SOURCE_BALE_BOT,
            'status' => Personnel::STATUS_PENDING,
            'tracking_code' => Personnel::generateTrackingCode(),
        ]);

        $center = Center::find($request->preferred_center_id);

        return response()->json([
            'success' => true,
            'message' => 'درخواست شما با موفقیت ثبت شد',
            'data' => [
                'tracking_code' => $personnel->tracking_code,
                'full_name' => $personnel->full_name,
                'national_code' => $personnel->national_code,
                'family_count' => $personnel->family_count,
                'preferred_center' => $center->name,
                'status' => 'در انتظار بررسی',
                'registered_at' => $personnel->created_at->format('Y/m/d H:i'),
            ]
        ], 201);
    }

    /**
     * Check request status by national code or tracking code
     */
    public function checkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'identifier_type' => 'required|in:national_code,tracking_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی',
                'errors' => $validator->errors()
            ], 422);
        }

        $field = $request->identifier_type;
        $personnel = Personnel::where($field, $request->identifier)->first();

        if (!$personnel) {
            return response()->json([
                'success' => false,
                'message' => 'درخواستی با این مشخصات یافت نشد'
            ], 404);
        }

        $statusText = match ($personnel->status) {
            Personnel::STATUS_PENDING => 'در انتظار بررسی',
            Personnel::STATUS_APPROVED => 'تأیید شده',
            Personnel::STATUS_REJECTED => 'رد شده',
            default => 'نامشخص',
        };

        $response = [
            'success' => true,
            'data' => [
                'tracking_code' => $personnel->tracking_code,
                'full_name' => $personnel->full_name,
                'national_code' => $personnel->national_code,
                'family_count' => $personnel->family_count,
                'status' => $statusText,
                'status_code' => $personnel->status,
                'registered_at' => $personnel->created_at->format('Y/m/d H:i'),
                'preferred_center' => $personnel->preferredCenter?->name,
            ]
        ];

        // If approved, include introduction letter info
        if ($personnel->status === Personnel::STATUS_APPROVED) {
            $letter = $personnel->introductionLetters()->active()->latest()->first();

            if ($letter) {
                $response['data']['introduction_letter'] = [
                    'letter_code' => $letter->letter_code,
                    'center' => $letter->center->name,
                    'family_count' => $letter->family_count,
                    'issued_at' => $letter->issued_at->format('Y/m/d H:i'),
                    'status' => $letter->status,
                ];
            }
        }

        // If rejected, include reason
        if ($personnel->status === Personnel::STATUS_REJECTED && $personnel->notes) {
            $response['data']['rejection_reason'] = $personnel->notes;
        }

        return response()->json($response);
    }

    /**
     * Get personnel introduction letters
     */
    public function getLetters(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'national_code' => 'required|string|size:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'کد ملی معتبر نیست',
                'errors' => $validator->errors()
            ], 422);
        }

        $personnel = Personnel::where('national_code', $request->national_code)->first();

        if (!$personnel) {
            return response()->json([
                'success' => false,
                'message' => 'پرسنلی با این کد ملی یافت نشد'
            ], 404);
        }

        $letters = $personnel->introductionLetters()
            ->with(['center', 'issuedBy'])
            ->latest()
            ->get()
            ->map(function ($letter) {
                return [
                    'letter_code' => $letter->letter_code,
                    'center' => $letter->center->name,
                    'family_count' => $letter->family_count,
                    'issued_at' => $letter->issued_at->format('Y/m/d H:i'),
                    'status' => $letter->status,
                    'is_active' => $letter->isActive(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'personnel' => [
                    'full_name' => $personnel->full_name,
                    'national_code' => $personnel->national_code,
                ],
                'letters' => $letters,
                'total' => $letters->count(),
            ]
        ]);
    }

    /**
     * Get available centers
     */
    public function getCenters()
    {
        $centers = Center::where('is_active', true)
            ->select('id', 'name', 'slug', 'city', 'type')
            ->get()
            ->map(function ($center) {
                return [
                    'id' => $center->id,
                    'name' => $center->name,
                    'city' => $center->city,
                    'type' => $center->type,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $centers
        ]);
    }
}
