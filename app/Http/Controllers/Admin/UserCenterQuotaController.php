<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\User;
use App\Models\UserCenterQuota;
use Illuminate\Http\Request;

class UserCenterQuotaController extends Controller
{
    public function index()
    {
        $users = User::with(['centerQuotas.center', 'roles'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $centers = Center::orderBy('name')->get();

        return view('admin.user-center-quota.index', compact('users', 'centers'));
    }

    public function update(Request $request, User $user, Center $center)
    {
        $request->validate([
            'quota_total' => 'required|integer|min:0|max:1000',
        ]);

        $quota = UserCenterQuota::where('user_id', $user->id)
            ->where('center_id', $center->id)
            ->firstOrFail();

        $quota->update([
            'quota_total' => $request->quota_total,
        ]);

        return redirect()
            ->route('admin.user-center-quota.index')
            ->with('success', "سهمیه {$user->name} برای {$center->name} به‌روزرسانی شد");
    }

    public function reset(User $user, Center $center)
    {
        $quota = UserCenterQuota::where('user_id', $user->id)
            ->where('center_id', $center->id)
            ->firstOrFail();

        $quota->update(['quota_used' => 0]);

        return redirect()
            ->route('admin.user-center-quota.index')
            ->with('success', "سهمیه استفاده شده {$user->name} برای {$center->name} بازنشانی شد");
    }

    public function bulkUpdate(Request $request, User $user)
    {
        $request->validate([
            'quotas' => 'required|array',
            'quotas.*' => 'required|integer|min:0|max:1000',
        ]);

        foreach ($request->quotas as $centerId => $quotaTotal) {
            UserCenterQuota::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'center_id' => $centerId,
                ],
                [
                    'quota_total' => $quotaTotal,
                ]
            );
        }

        return redirect()
            ->route('admin.user-center-quota.index')
            ->with('success', "سهمیه‌های {$user->name} به‌روزرسانی شد");
    }
}
