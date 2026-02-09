<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Http\Requests\StoreCenterRequest;
use App\Http\Requests\UpdateCenterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;

class CenterController extends Controller
{
    use AuthorizesRequests;

    /**
     * نمایش لیست مراکز رفاهی
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Center::class);

        // استفاده از withCount برای جلوگیری از N+1 Query
        $query = Center::withCount(['units', 'periods']);

        // جستجو
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // فیلتر نوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فیلتر وضعیت
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $centers = $query->orderBy('name')->paginate(10);

        return view('centers.index', compact('centers'));
    }

    /**
     * نمایش فرم ایجاد مرکز جدید
     */
    public function create()
    {
        $this->authorize('create', Center::class);

        return view('centers.create');
    }

    /**
     * ذخیره مرکز جدید
     */
    public function store(StoreCenterRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name'], '-');

        Center::create($validated);

        return redirect()->route('centers.index')
            ->with('success', 'مرکز رفاهی با موفقیت ایجاد شد.');
    }

    /**
     * نمایش جزئیات مرکز
     */
    public function show(Center $center)
    {
        $this->authorize('view', $center);

        // استفاده از withCount و eager loading بهینه
        $center->loadCount('units');
        $center->load(['periods' => function ($query) {
            $query->orderBy('start_date', 'desc')->limit(5);
        }]);

        // آمار واحدها به تفکیک نوع
        $unitStats = Cache::remember("center_{$center->id}_unit_stats", 300, function () use ($center) {
            return $center->units()
                ->selectRaw('type, COUNT(*) as count, SUM(bed_count) as beds')
                ->groupBy('type')
                ->get()
                ->keyBy('type');
        });

        return view('centers.show', compact('center', 'unitStats'));
    }

    /**
     * نمایش فرم ویرایش مرکز
     */
    public function edit(Center $center)
    {
        $this->authorize('update', $center);

        return view('centers.edit', compact('center'));
    }

    /**
     * بروزرسانی مرکز
     */
    public function update(UpdateCenterRequest $request, Center $center)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name'], '-');

        $center->update($validated);

        // پاک کردن cache آمار
        Cache::forget("center_{$center->id}_unit_stats");

        return redirect()->route('centers.index')
            ->with('success', 'مرکز رفاهی با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف مرکز
     */
    public function destroy(Center $center)
    {
        $this->authorize('delete', $center);

        // بررسی وابستگی‌ها
        if ($center->units()->count() > 0) {
            return back()->with('error', 'این مرکز دارای واحدهای ثبت شده است و قابل حذف نیست.');
        }

        if ($center->periods()->count() > 0) {
            return back()->with('error', 'این مرکز دارای دوره‌های ثبت شده است و قابل حذف نیست.');
        }

        $center->delete();

        return redirect()->route('centers.index')
            ->with('success', 'مرکز رفاهی با موفقیت حذف شد.');
    }

    /**
     * تغییر وضعیت فعال/غیرفعال
     */
    public function toggleStatus(Center $center)
    {
        $this->authorize('toggleStatus', $center);

        $center->update(['is_active' => !$center->is_active]);

        $status = $center->is_active ? 'فعال' : 'غیرفعال';
        return back()->with('success', "مرکز {$center->name} {$status} شد.");
    }
}
