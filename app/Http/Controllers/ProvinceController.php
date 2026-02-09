<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Services\QuotaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProvinceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private QuotaService $quotaService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Province::class);

        $query = Province::withCount('personnel');

        // فیلتر نوع
        if ($request->boolean('is_tehran')) {
            $query->where('is_tehran', true);
        } elseif ($request->filled('is_tehran') && $request->is_tehran === '0') {
            $query->where('is_tehran', false);
        }

        // جستجو
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $provinces = $query->orderBy('name')->paginate(40);

        // محاسبه آمار کلی
        $stats = [
            'total_provinces' => Province::count(),
            'total_personnel' => Province::sum('personnel_count'),
            'tehran_offices' => Province::where('is_tehran', true)->count(),
        ];

        return view('provinces.index', compact('provinces', 'stats'));
    }

    public function show(Province $province)
    {
        $this->authorize('view', $province);

        $province->loadCount('personnel');

        // سهمیه برای هر مرکز
        $quotas = $this->quotaService->calculateQuotasForProvince($province);

        // آمار پرسنل
        $personnelStats = [
            'active' => $province->personnel()->where('employment_status', 'active')->count(),
            'retired' => $province->personnel()->where('employment_status', 'retired')->count(),
            'isargar' => $province->personnel()->where('is_isargar', true)->count(),
        ];

        return view('provinces.show', compact('province', 'quotas', 'personnelStats'));
    }

    public function edit(Province $province)
    {
        $this->authorize('update', $province);

        return view('provinces.edit', compact('province'));
    }

    public function update(Request $request, Province $province)
    {
        $this->authorize('update', $province);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:provinces,code,' . $province->id,
            'personnel_count' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'نام استان الزامی است.',
            'code.required' => 'کد استان الزامی است.',
            'code.unique' => 'این کد قبلاً استفاده شده است.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // محاسبه نسبت سهمیه
        $totalPersonnel = Province::sum('personnel_count');
        if ($totalPersonnel > 0) {
            $validated['quota_ratio'] = $validated['personnel_count'] / $totalPersonnel;
        }

        $province->update($validated);

        // بروزرسانی نسبت سهمیه همه استان‌ها
        $this->quotaService->recalculateAllQuotaRatios();

        return redirect()->route('provinces.show', $province)
            ->with('success', 'اطلاعات استان با موفقیت بروزرسانی شد.');
    }

    public function recalculateQuotas()
    {
        $this->authorize('update', new Province());

        $this->quotaService->recalculateAllQuotaRatios();

        return back()->with('success', 'نسبت سهمیه همه استان‌ها بروزرسانی شد.');
    }
}
