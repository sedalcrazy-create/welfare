<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Center;
use App\Models\UserCenterQuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * لیست کاربران
     */
    public function index()
    {
        $users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * فرم ساخت کاربر جدید
     */
    public function create()
    {
        $roles = Role::all();
        $centers = Center::where('is_active', true)->get();

        return view('admin.users.create', compact('roles', 'centers'));
    }

    /**
     * ذخیره کاربر جدید
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'quotas' => 'nullable|array',
            'quotas.*' => 'nullable|integer|min:0|max:999',
        ]);

        // ساخت کاربر
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->boolean('is_active', true),
        ]);

        // اختصاص نقش
        $user->assignRole($request->role);

        // اختصاص سهمیه‌ها (اگر ارسال شده)
        if ($request->filled('quotas')) {
            foreach ($request->quotas as $centerId => $quota) {
                if ($quota > 0) {
                    UserCenterQuota::create([
                        'user_id' => $user->id,
                        'center_id' => $centerId,
                        'total_quota' => $quota,
                        'used_quota' => 0,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'کاربر با موفقیت ساخته شد.');
    }

    /**
     * نمایش جزئیات کاربر
     */
    public function show(User $user)
    {
        $user->load(['roles', 'centerQuotas.center']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * فرم ویرایش کاربر
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $centers = Center::where('is_active', true)->get();
        $user->load(['roles', 'centerQuotas']);

        // سهمیه‌های فعلی کاربر به صورت آرایه
        $userQuotas = $user->centerQuotas->pluck('total_quota', 'center_id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'centers', 'userQuotas'));
    }

    /**
     * بروزرسانی کاربر
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'quotas' => 'nullable|array',
            'quotas.*' => 'nullable|integer|min:0|max:999',
        ]);

        // بروزرسانی اطلاعات کاربر
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->boolean('is_active'),
        ]);

        // بروزرسانی رمز عبور (اگر وارد شده)
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        // بروزرسانی نقش
        $user->syncRoles([$request->role]);

        // بروزرسانی سهمیه‌ها
        if ($request->has('quotas')) {
            foreach ($request->quotas as $centerId => $quota) {
                UserCenterQuota::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'center_id' => $centerId
                    ],
                    [
                        'total_quota' => $quota ?? 0
                    ]
                );
            }
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'کاربر با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف کاربر
     */
    public function destroy(User $user)
    {
        // جلوگیری از حذف خودش
        if ($user->id === auth()->id()) {
            return back()->with('error', 'نمی‌توانید خودتان را حذف کنید!');
        }

        // حذف سهمیه‌ها
        $user->centerQuotas()->delete();

        // حذف کاربر
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'کاربر با موفقیت حذف شد.');
    }

    /**
     * تغییر وضعیت (فعال/غیرفعال)
     */
    public function toggleStatus(User $user)
    {
        // جلوگیری از غیرفعال کردن خودش
        if ($user->id === auth()->id()) {
            return back()->with('error', 'نمی‌توانید وضعیت خودتان را تغییر دهید!');
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'فعال' : 'غیرفعال';

        return back()->with('success', "کاربر با موفقیت {$status} شد.");
    }
}
