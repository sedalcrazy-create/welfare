<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Center;
use App\Models\UserCenterQuota;
use App\Services\UserQuotaService;
use App\Http\Requests\AllocateQuotaRequest;
use App\Http\Requests\UpdateQuotaRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuotaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private UserQuotaService $quotaService
    ) {}

    /**
     * Display quota summary for a specific user
     */
    public function index(User $user)
    {
        $this->authorize('viewAny', UserCenterQuota::class);

        $quotaSummary = $this->quotaService->getQuotaSummary($user);
        $centers = Center::where('is_active', true)->orderBy('name')->get();

        return view('admin.quotas.index', compact('user', 'quotaSummary', 'centers'));
    }

    /**
     * Allocate quota to a user for a specific center
     */
    public function allocate(AllocateQuotaRequest $request, User $user)
    {
        $this->authorize('allocate', UserCenterQuota::class);

        try {
            $center = Center::findOrFail($request->center_id);

            $quota = $this->quotaService->allocateQuota(
                $user,
                $center,
                $request->quota_total
            );

            return redirect()
                ->back()
                ->with('success', "سهمیه {$center->name} برای {$user->name} تخصیص داده شد");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در تخصیص سهمیه: ' . $e->getMessage());
        }
    }

    /**
     * Update quota for a user-center pair
     */
    public function update(UpdateQuotaRequest $request, UserCenterQuota $quota)
    {
        $this->authorize('update', UserCenterQuota::class);

        try {
            $oldTotal = $quota->quota_total;
            $quota->update(['quota_total' => $request->quota_total]);

            $action = $request->quota_total > $oldTotal ? 'افزایش' : 'کاهش';
            $diff = abs($request->quota_total - $oldTotal);

            return redirect()
                ->back()
                ->with('success', "سهمیه {$action} یافت ({$diff} عدد)");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در بروزرسانی سهمیه: ' . $e->getMessage());
        }
    }

    /**
     * Reset used quota for a user-center pair
     */
    public function reset(UserCenterQuota $quota)
    {
        $this->authorize('reset', UserCenterQuota::class);

        try {
            $usedCount = $quota->quota_used;

            $this->quotaService->resetUsedQuota(
                $quota->user,
                $quota->center
            );

            return redirect()
                ->back()
                ->with('success', "سهمیه استفاده شده بازنشانی شد ({$usedCount} عدد برگشت داده شد)");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در بازنشانی سهمیه: ' . $e->getMessage());
        }
    }

    /**
     * Increase quota
     */
    public function increase(Request $request, UserCenterQuota $quota)
    {
        $this->authorize('update', UserCenterQuota::class);

        $request->validate([
            'amount' => 'required|integer|min:1|max:100'
        ], [
            'amount.required' => 'مقدار افزایش الزامی است',
            'amount.min' => 'مقدار افزایش باید حداقل 1 باشد',
        ]);

        try {
            $this->quotaService->increaseQuota(
                $quota->user,
                $quota->center,
                $request->amount
            );

            return redirect()
                ->back()
                ->with('success', "سهمیه {$request->amount} عدد افزایش یافت");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در افزایش سهمیه: ' . $e->getMessage());
        }
    }

    /**
     * Decrease quota
     */
    public function decrease(Request $request, UserCenterQuota $quota)
    {
        $this->authorize('update', UserCenterQuota::class);

        $request->validate([
            'amount' => 'required|integer|min:1|max:' . $quota->quota_total
        ], [
            'amount.required' => 'مقدار کاهش الزامی است',
            'amount.min' => 'مقدار کاهش باید حداقل 1 باشد',
            'amount.max' => 'مقدار کاهش بیش از سهمیه کل است',
        ]);

        try {
            $this->quotaService->decreaseQuota(
                $quota->user,
                $quota->center,
                $request->amount
            );

            return redirect()
                ->back()
                ->with('success', "سهمیه {$request->amount} عدد کاهش یافت");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در کاهش سهمیه: ' . $e->getMessage());
        }
    }
}
