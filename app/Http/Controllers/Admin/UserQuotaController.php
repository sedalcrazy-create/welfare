<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserQuotaController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.user-quota.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'quota_total' => 'required|integer|min:0|max:1000',
        ]);

        $user->update([
            'quota_total' => $request->quota_total,
        ]);

        return redirect()
            ->route('admin.user-quota.index')
            ->with('success', 'سهمیه کاربر با موفقیت به‌روزرسانی شد');
    }

    public function resetUsed(User $user)
    {
        $user->update(['quota_used' => 0]);

        return redirect()
            ->route('admin.user-quota.index')
            ->with('success', 'سهمیه استفاده شده کاربر بازنشانی شد');
    }
}
