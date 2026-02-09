<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Center;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;

class UnitController extends Controller
{
    use AuthorizesRequests;

    /**
     * نمایش لیست واحدها
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Unit::class);

        // استفاده از withCount برای جلوگیری از N+1 Query
        $query = Unit::with('center')->withCount('reservations');

        // فیلتر مرکز
        if ($request->filled('center_id')) {
            $query->where('center_id', $request->center_id);
        }

        // جستجو
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // فیلتر نوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فیلتر وضعیت
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فیلتر تعداد تخت
        if ($request->filled('bed_count')) {
            $query->where('bed_count', $request->bed_count);
        }

        $units = $query->orderBy('center_id')->orderBy('number')->paginate(20);

        // Cache لیست مراکز
        $centers = Cache::remember('active_centers', 600, function () {
            return Center::orderBy('name')->get();
        });

        return view('units.index', compact('units', 'centers'));
    }

    /**
     * نمایش فرم ایجاد واحد جدید
     */
    public function create(Request $request)
    {
        $this->authorize('create', Unit::class);

        $centers = Center::where('is_active', true)->orderBy('name')->get();
        $selectedCenter = $request->center_id;

        return view('units.create', compact('centers', 'selectedCenter'));
    }

    /**
     * ذخیره واحد جدید
     */
    public function store(StoreUnitRequest $request)
    {
        $validated = $request->validated();

        Unit::create($validated);

        // بروزرسانی تعداد واحد و تخت مرکز
        $this->updateCenterCounts($validated['center_id']);

        // پاک کردن cache آمار مرکز
        Cache::forget("center_{$validated['center_id']}_unit_stats");

        return redirect()->route('units.index', ['center_id' => $validated['center_id']])
            ->with('success', 'واحد جدید با موفقیت ایجاد شد.');
    }

    /**
     * نمایش جزئیات واحد
     */
    public function show(Unit $unit)
    {
        $this->authorize('view', $unit);

        $unit->load('center');
        $unit->loadCount('reservations');

        // آمار رزروها
        $reservationStats = Cache::remember("unit_{$unit->id}_reservation_stats", 300, function () use ($unit) {
            return [
                'total' => $unit->reservations()->count(),
                'confirmed' => $unit->reservations()->where('status', 'confirmed')->count(),
                'checked_in' => $unit->reservations()->where('status', 'checked_in')->count(),
                'completed' => $unit->reservations()->where('status', 'completed')->count(),
            ];
        });

        return view('units.show', compact('unit', 'reservationStats'));
    }

    /**
     * نمایش فرم ویرایش واحد
     */
    public function edit(Unit $unit)
    {
        $this->authorize('update', $unit);

        $centers = Center::where('is_active', true)->orderBy('name')->get();

        return view('units.edit', compact('unit', 'centers'));
    }

    /**
     * بروزرسانی واحد
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $validated = $request->validated();
        $oldCenterId = $unit->center_id;

        $unit->update($validated);

        // بروزرسانی تعداد واحد و تخت مرکز
        $this->updateCenterCounts($validated['center_id']);
        if ($oldCenterId !== (int)$validated['center_id']) {
            $this->updateCenterCounts($oldCenterId);
            Cache::forget("center_{$oldCenterId}_unit_stats");
        }

        // پاک کردن cache
        Cache::forget("center_{$validated['center_id']}_unit_stats");
        Cache::forget("unit_{$unit->id}_reservation_stats");

        return redirect()->route('units.index', ['center_id' => $validated['center_id']])
            ->with('success', 'واحد با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف واحد
     */
    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit);

        $centerId = $unit->center_id;

        // بررسی رزروهای فعال
        if ($unit->reservations()->whereIn('status', ['confirmed', 'checked_in'])->exists()) {
            return back()->with('error', 'این واحد دارای رزرو فعال است و قابل حذف نیست.');
        }

        $unit->delete();

        // بروزرسانی تعداد واحد و تخت مرکز
        $this->updateCenterCounts($centerId);

        return redirect()->route('units.index', ['center_id' => $centerId])
            ->with('success', 'واحد با موفقیت حذف شد.');
    }

    /**
     * تغییر وضعیت واحد
     */
    public function toggleStatus(Unit $unit, Request $request)
    {
        $this->authorize('toggleStatus', $unit);

        $newStatus = $request->status;

        if (!in_array($newStatus, ['active', 'maintenance', 'blocked'])) {
            return back()->with('error', 'وضعیت نامعتبر است.');
        }

        $unit->update(['status' => $newStatus]);

        $statusLabels = [
            'active' => 'فعال',
            'maintenance' => 'در حال تعمیر',
            'blocked' => 'مسدود',
        ];

        return back()->with('success', "وضعیت واحد به «{$statusLabels[$newStatus]}» تغییر یافت.");
    }

    /**
     * بروزرسانی تعداد واحد و تخت مرکز
     */
    private function updateCenterCounts(int $centerId): void
    {
        $center = Center::find($centerId);
        if ($center) {
            $center->update([
                'unit_count' => Unit::where('center_id', $centerId)->count(),
                'bed_count' => Unit::where('center_id', $centerId)->sum('bed_count'),
            ]);
        }
    }

    /**
     * ایمپورت واحدها از اکسل
     */
    public function import(Request $request)
    {
        $this->authorize('import', Unit::class);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'center_id' => 'required|exists:centers,id',
        ]);

        // TODO: Implement Excel import
        return back()->with('info', 'قابلیت ایمپورت به زودی فعال می‌شود.');
    }
}
