<?php

namespace App\Http\Controllers\Api\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BaleVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private BaleVerificationService $baleVerification;

    public function __construct(BaleVerificationService $baleVerification)
    {
        $this->baleVerification = $baleVerification;
    }

    /**
     * Verify Bale Mini App initData and authenticate user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'initData' => 'required|string'
        ]);

        // Verify initData with Bale
        $baleUser = $this->baleVerification->verifyInitData($request->initData);

        if (!$baleUser) {
            return response()->json([
                'success' => false,
                'message' => 'احراز هویت با بله ناموفق بود. لطفاً دوباره تلاش کنید.'
            ], 401);
        }

        // Extract user data
        $userData = $this->baleVerification->extractUserData($baleUser);

        if (!$userData['bale_user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات کاربر بله نامعتبر است.'
            ], 400);
        }

        // Find or create user
        $user = User::where('bale_user_id', $userData['bale_user_id'])->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'bale_user_id' => $userData['bale_user_id'],
                'name' => trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '')) ?: 'کاربر بله',
                'email' => $userData['username']
                    ? $userData['username'] . '@bale.user'
                    : 'bale_' . $userData['bale_user_id'] . '@bale.user',
                'password' => Hash::make(Str::random(32)), // Random password (won't be used)
                'is_active' => true,
            ]);

            // Assign default 'user' role
            $user->assignRole('user');

            \Log::info('New user created from Bale Mini App', [
                'user_id' => $user->id,
                'bale_user_id' => $userData['bale_user_id']
            ]);
        } else {
            // Update user info if changed
            $user->update([
                'name' => trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '')) ?: $user->name,
            ]);
        }

        // Generate Sanctum token
        $token = $user->createToken('bale-mini-app')->plainTextToken;

        // Load relationships
        $user->load('roles', 'personnel');

        return response()->json([
            'success' => true,
            'message' => 'ورود موفقیت‌آمیز بود.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'bale_user_id' => $user->bale_user_id,
                    'roles' => $user->roles->pluck('name'),
                    'is_active' => $user->is_active,
                ],
                'personnel' => $user->personnel ? [
                    'id' => $user->personnel->id,
                    'employee_code' => $user->personnel->employee_code,
                    'national_code' => $user->personnel->national_code,
                    'full_name' => $user->personnel->full_name,
                    'status' => $user->personnel->status,
                    'province' => [
                        'id' => $user->personnel->province->id,
                        'name' => $user->personnel->province->name,
                    ],
                ] : null,
                'has_personnel' => $user->personnel !== null,
                'personnel_status' => $user->personnel?->status,
            ]
        ]);
    }

    /**
     * Get current authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('roles', 'personnel.province');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'bale_user_id' => $user->bale_user_id,
                    'roles' => $user->roles->pluck('name'),
                    'is_active' => $user->is_active,
                ],
                'personnel' => $user->personnel ? [
                    'id' => $user->personnel->id,
                    'employee_code' => $user->personnel->employee_code,
                    'national_code' => $user->personnel->national_code,
                    'full_name' => $user->personnel->full_name,
                    'status' => $user->personnel->status,
                    'province' => [
                        'id' => $user->personnel->province->id,
                        'name' => $user->personnel->province->name,
                    ],
                ] : null,
                'has_personnel' => $user->personnel !== null,
                'personnel_status' => $user->personnel?->status,
            ]
        ]);
    }

    /**
     * Logout (revoke token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'خروج موفقیت‌آمیز بود.'
        ]);
    }
}
