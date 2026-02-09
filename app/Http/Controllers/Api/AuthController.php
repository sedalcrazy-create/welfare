<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'employee_code' => 'required|string',
            'national_code' => 'required|string|size:10',
        ]);

        $personnel = Personnel::where('employee_code', $request->employee_code)
            ->where('national_code', $request->national_code)
            ->first();

        if (!$personnel) {
            throw ValidationException::withMessages([
                'employee_code' => ['اطلاعات وارد شده صحیح نیست.'],
            ]);
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['personnel_id' => $personnel->id],
            [
                'name' => $personnel->full_name,
                'email' => $personnel->email ?? $personnel->employee_code . '@bankmelli.ir',
                'password' => Hash::make($personnel->national_code),
                'province_id' => $personnel->province_id,
                'phone' => $personnel->phone,
            ]
        );

        $user->assignRole('user');
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'employee_code' => $personnel->employee_code,
                'province' => $personnel->province->name,
            ],
        ]);
    }

    public function baleVerify(Request $request)
    {
        $request->validate([
            'bale_user_id' => 'required|string',
            'init_data' => 'required|string',
        ]);

        // TODO: Verify init_data with Bale API

        $personnel = Personnel::where('bale_user_id', $request->bale_user_id)->first();

        if (!$personnel) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر یافت نشد. لطفاً ابتدا ثبت‌نام کنید.',
                'requires_registration' => true,
            ], 404);
        }

        $user = User::where('personnel_id', $personnel->id)->first();

        if (!$user) {
            $user = User::create([
                'name' => $personnel->full_name,
                'email' => $personnel->email ?? $personnel->employee_code . '@bankmelli.ir',
                'password' => Hash::make($personnel->national_code),
                'personnel_id' => $personnel->id,
                'province_id' => $personnel->province_id,
                'bale_user_id' => $request->bale_user_id,
            ]);
            $user->assignRole('user');
        }

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('bale-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'employee_code' => $personnel->employee_code,
            ],
        ]);
    }
}
