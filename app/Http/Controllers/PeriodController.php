<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Center;
use App\Models\Season;
use App\Services\PeriodService;
use App\Services\CacheService;
use App\Http\Requests\StorePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;

class PeriodController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private PeriodService $periodService,
        private CacheService $cacheService
    ) {}

    /**
     * نمایش لیست دوره‌ها
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Period::class);

        // استفاده از withCount برای جلوگیری از N+1 Query
        $query = Period::with(['center', 'season'])
            ->withCount(['reservations', 'lottery as has_lottery']);

        // فیلتر مرکز
        if ($request->filled('center_id')) {
            $query->where('center_id', $request->center_id);
        }

        // فیلتر وضعیت
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فیلتر تاریخ
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        // جستجو
        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        $periods = $query->orderBy('start_date', 'desc')->paginate(15);

        // Cache لیست مراکز فعال
        $centers = Cache::remember('active_centers', 600, function () {
            return Center::where('is_active', true)->orderBy('name')->get();
        });

        return view('periods.index', compact('periods', 'centers'));
    }

    /**
     * نمایش فرم ایجاد دوره جدید
     */
    public function create(Request $request)
    {
        $this->authorize('create', Period::class);

        $centers = Center::where('is_active', true)->orderBy('name')->get();
        $seasons = collect();

        if ($request->filled('center_id')) {
            $seasons = Season::where('center_id', $request->center_id)
                ->where('is_active', true)
                ->orderBy('start_date')
                ->get();
        }

        $selectedCenter = $request->center_id;

        return view('periods.create', compact('centers', 'seasons', 'selectedCenter'));
    }

    /**
     * ذخیره دوره جدید
     */
    public function store(StorePeriodRequest $request)
    {
        $validated = $request->validated();

        if (!$this->periodService->validateDates($validated['jalali_start_date'], $validated['jalali_end_date'])) {
            return back()->withInput()->with('error', 'تاریخ پایان باید بعد از تاریخ شروع باشد.');
        }

        $this->periodService->createPeriod($validated);

        return redirect()->route('periods.index', ['center_id' => $validated['center_id']])
            ->with('success', 'دوره جدید با موفقیت ایجاد شد.');
    }

    /**
     * نمایش جزئیات دوره
     */
    public function show(Period $period)
    {
        $this->authorize('view', $period);

        $period->load(['center', 'season', 'lottery']);
        $period->loadCount('reservations');

        // آمار دوره با Cache
        $periodStats = Cache::remember("period_{$period->id}_stats", 300, function () use ($period) {
            return [
                'total_reservations' => $period->reservations()->count(),
                'confirmed' => $period->reservations()->where('status', 'confirmed')->count(),
                'checked_in' => $period->reservations()->where('status', 'checked_in')->count(),
                'completed' => $period->reservations()->where('status', 'completed')->count(),
                'cancelled' => $period->reservations()->where('status', 'cancelled')->count(),
                'occupancy_rate' => $period->capacity > 0
                    ? round(($period->reserved_count / $period->capacity) * 100, 1)
                    : 0,
            ];
        });

        // رزروهای اخیر با pagination
        $recentReservations = $period->reservations()
            ->with(['personnel', 'unit'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('periods.show', compact('period', 'periodStats', 'recentReservations'));
    }

    /**
     * نمایش فرم ویرایش دوره
     */
    public function edit(Period $period)
    {
        $this->authorize('update', $period);

        $centers = Center::where('is_active', true)->orderBy('name')->get();
        $seasons = Season::where('center_id', $period->center_id)
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get();

        return view('periods.edit', compact('period', 'centers', 'seasons'));
    }

    /**
     * بروزرسانی دوره
     */
    public function update(UpdatePeriodRequest $request, Period $period)
    {
        if (!$this->periodService->canUpdate($period)) {
            return back()->with('error', 'این دوره دارای قرعه‌کشی فعال است و امکان ویرایش وجود ندارد.');
        }

        $validated = $request->validated();

        if (!$this->periodService->validateDates($validated['jalali_start_date'], $validated['jalali_end_date'])) {
            return back()->withInput()->with('error', 'تاریخ پایان باید بعد از تاریخ شروع باشد.');
        }

        $this->periodService->updatePeriod($period, $validated);

        // پاک کردن cache
        $this->cacheService->forgetPeriodCache($period->id);

        return redirect()->route('periods.index', ['center_id' => $validated['center_id']])
            ->with('success', 'دوره با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف دوره
     */
    public function destroy(Period $period)
    {
        $this->authorize('delete', $period);

        [$canDelete, $error] = $this->periodService->canDelete($period);
        if (!$canDelete) {
            return back()->with('error', $error);
        }

        $centerId = $period->center_id;
        $periodId = $period->id;
        $period->delete();

        // پاک کردن cache
        $this->cacheService->forgetPeriodCache($periodId);

        return redirect()->route('periods.index', ['center_id' => $centerId])
            ->with('success', 'دوره با موفقیت حذف شد.');
    }

    /**
     * تغییر وضعیت دوره
     */
    public function changeStatus(Period $period, Request $request)
    {
        $this->authorize('changeStatus', $period);

        $newStatus = $request->status;

        if (!in_array($newStatus, ['draft', 'open', 'closed', 'completed'])) {
            return back()->with('error', 'وضعیت نامعتبر است.');
        }

        // بررسی منطق تغییر وضعیت
        if ($newStatus === 'open' && $period->start_date < now()) {
            return back()->with('error', 'دوره‌ای که تاریخ شروعش گذشته نمی‌تواند باز شود.');
        }

        $period->update(['status' => $newStatus]);

        $statusLabels = [
            'draft' => 'پیش‌نویس',
            'open' => 'باز',
            'closed' => 'بسته',
            'completed' => 'تکمیل شده',
        ];

        return back()->with('success', "وضعیت دوره به «{$statusLabels[$newStatus]}» تغییر یافت.");
    }

    /**
     * دریافت فصل‌های یک مرکز (AJAX)
     */
    public function getSeasons(Center $center)
    {
        $seasons = Season::where('center_id', $center->id)
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get(['id', 'name', 'type']);

        return response()->json($seasons);
    }

}
