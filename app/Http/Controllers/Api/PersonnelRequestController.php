<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Center;
use App\Models\Period;
use App\Services\PersonnelRequestService;
use App\Http\Requests\Api\RegisterPersonnelRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PersonnelRequestController extends Controller
{
    public function __construct(
        private PersonnelRequestService $personnelService
    ) {}

    /**
     * Register a new personnel request from Bale bot
     */
    public function register(RegisterPersonnelRequest $request)
    {
        try {
            // Create personnel using service
            $data = $request->validated();
            $data['registration_source'] = Personnel::SOURCE_BALE_BOT;

            // Note: In Phase 1, we don't check quota at registration time
            // Quota is checked when issuing introduction letter
            $personnel = Personnel::create([
                'employee_code' => $data['employee_code'],
                'full_name' => $data['full_name'],
                'national_code' => $data['national_code'],
                'phone' => $data['phone'],
                'preferred_center_id' => $data['preferred_center_id'],
                'preferred_period_id' => $data['preferred_period_id'],
                'bale_user_id' => $data['bale_user_id'] ?? null,
                'family_members' => $data['family_members'] ?? null,
                'registration_source' => Personnel::SOURCE_BALE_BOT,
                'status' => Personnel::STATUS_PENDING,
                'tracking_code' => Personnel::generateTrackingCode(),
            ]);

            $center = Center::find($personnel->preferred_center_id);
            $period = Period::find($personnel->preferred_period_id);

            return response()->json([
                'success' => true,
                'message' => 'درخواست شما با موفقیت ثبت شد',
                'data' => [
                    'tracking_code' => $personnel->tracking_code,
                    'employee_code' => $personnel->employee_code,
                    'full_name' => $personnel->full_name,
                    'national_code' => $personnel->national_code,
                    'total_persons' => $personnel->getTotalPersonsCount(),
                    'family_members_count' => $personnel->getFamilyMembersCount(),
                    'preferred_center' => $center->name,
                    'preferred_period' => $period->title,
                    'period_dates' => $period->start_date_jalali . ' تا ' . $period->end_date_jalali,
                    'status' => 'در انتظار بررسی',
                    'status_code' => Personnel::STATUS_PENDING,
                    'registered_at' => $personnel->created_at->format('Y/m/d H:i'),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت درخواست: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check request status by tracking code
     */
    public function checkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tracking_code' => 'required|string',
        ], [
            'tracking_code.required' => 'کد پیگیری الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی',
                'errors' => $validator->errors()
            ], 422);
        }

        $personnel = $this->personnelService->findByTrackingCode($request->tracking_code);

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
                'preferred_period' => $personnel->preferredPeriod?->title,
            ]
        ];

        // If has period, include period info
        if ($personnel->preferredPeriod) {
            $response['data']['period_info'] = [
                'title' => $personnel->preferredPeriod->title,
                'start_date' => $personnel->preferredPeriod->start_date_jalali,
                'end_date' => $personnel->preferredPeriod->end_date_jalali,
            ];
        }

        // If approved, include introduction letter info
        if ($personnel->status === Personnel::STATUS_APPROVED) {
            $letter = $personnel->introductionLetters()->active()->latest()->first();

            if ($letter) {
                $response['data']['introduction_letter'] = [
                    'letter_code' => $letter->letter_code,
                    'center' => $letter->center->name,
                    'period' => $letter->period?->title,
                    'family_count' => $letter->family_count,
                    'issued_at' => $letter->issued_at->format('Y/m/d H:i'),
                    'status' => $letter->status,
                    'valid_from' => $letter->valid_from,
                    'valid_until' => $letter->valid_until,
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
            ->with(['center', 'period', 'issuedBy'])
            ->latest()
            ->get()
            ->map(function ($letter) {
                return [
                    'letter_code' => $letter->letter_code,
                    'center' => $letter->center->name,
                    'period' => $letter->period?->title,
                    'period_dates' => $letter->period ?
                        $letter->period->start_date_jalali . ' تا ' . $letter->period->end_date_jalali : null,
                    'family_count' => $letter->family_count,
                    'issued_at' => $letter->issued_at->format('Y/m/d H:i'),
                    'valid_from' => $letter->valid_from,
                    'valid_until' => $letter->valid_until,
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
