<?php

namespace App\Http\Controllers;

use App\Models\Lottery;
use App\Models\LotteryEntry;
use App\Models\Period;
use App\Models\Center;
use App\Services\LotteryService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;

class LotteryController extends Controller
{
    use AuthorizesRequests;

    protected LotteryService $lotteryService;

    public function __construct(LotteryService $lotteryService)
    {
        $this->lotteryService = $lotteryService;
    }

    /**
     * نمایش لیست قرعه‌کشی‌ها
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lottery::class);

        // استفاده از withCount برای جلوگیری از N+1 Query
        $query = Lottery::with(['period.center'])
            ->withCount('entries');

        // فیلتر مرکز
        if ($request->filled('center_id')) {
            $query->whereHas('period', function ($q) use ($request) {
                $q->where('center_id', $request->center_id);
            });
        }

        // فیلتر وضعیت
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // جستجو
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $lotteries = $query->orderBy('created_at', 'desc')->paginate(15);

        // Cache لیست مراکز فعال
        $centers = Cache::remember('active_centers', 600, function () {
            return Center::where('is_active', true)->orderBy('name')->get();
        });

        return view('lotteries.index', compact('lotteries', 'centers'));
    }

    /**
     * نمایش فرم ایجاد قرعه‌کشی جدید
     */
    public function create(Request $request)
    {
        $this->authorize('create', Lottery::class);

        $centers = Center::where('is_active', true)->orderBy('name')->get();
        $periods = collect();

        if ($request->filled('center_id')) {
            $periods = Period::where('center_id', $request->center_id)
                ->whereIn('status', ['draft', 'open'])
                ->whereDoesntHave('lottery')
                ->orderBy('start_date')
                ->get();
        }

        return view('lotteries.create', compact('centers', 'periods'));
    }

    /**
     * ذخیره قرعه‌کشی جدید
     */
    public function store(Request $request)
    {
        $this->authorize('create', Lottery::class);

        $validated = $request->validate([
            'period_id' => 'required|exists:periods,id|unique:lotteries,period_id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'jalali_registration_start' => 'required|string|size:10',
            'jalali_registration_end' => 'required|string|size:10',
            'jalali_draw_date' => 'required|string|size:10',
            'algorithm' => 'required|in:weighted_random,priority_based',
            'status' => 'required|in:draft,open',
        ], [
            'period_id.required' => 'انتخاب دوره الزامی است.',
            'period_id.unique' => 'این دوره قبلاً قرعه‌کشی دارد.',
            'title.required' => 'عنوان الزامی است.',
            'jalali_registration_start.required' => 'تاریخ شروع ثبت‌نام الزامی است.',
            'jalali_registration_end.required' => 'تاریخ پایان ثبت‌نام الزامی است.',
            'jalali_draw_date.required' => 'تاریخ قرعه‌کشی الزامی است.',
        ]);

        // تبدیل تاریخ‌ها
        $registrationStart = $this->jalaliToCarbon($validated['jalali_registration_start']);
        $registrationEnd = $this->jalaliToCarbon($validated['jalali_registration_end'])->endOfDay();
        $drawDate = $this->jalaliToCarbon($validated['jalali_draw_date']);

        if ($registrationEnd <= $registrationStart) {
            return back()->withInput()->with('error', 'تاریخ پایان ثبت‌نام باید بعد از تاریخ شروع باشد.');
        }

        if ($drawDate < $registrationEnd) {
            return back()->withInput()->with('error', 'تاریخ قرعه‌کشی باید بعد از پایان ثبت‌نام باشد.');
        }

        Lottery::create([
            'period_id' => $validated['period_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'registration_start_date' => $registrationStart,
            'registration_end_date' => $registrationEnd,
            'draw_date' => $drawDate,
            'algorithm' => $validated['algorithm'],
            'status' => $validated['status'],
            'total_participants' => 0,
            'total_winners' => 0,
        ]);

        return redirect()->route('lotteries.index')
            ->with('success', 'قرعه‌کشی جدید با موفقیت ایجاد شد.');
    }

    /**
     * نمایش جزئیات قرعه‌کشی
     */
    public function show(Lottery $lottery)
    {
        $this->authorize('view', $lottery);

        $lottery->load(['period.center']);

        // آمار با Cache
        $stats = Cache::remember("lottery_{$lottery->id}_stats", 120, function () use ($lottery) {
            return [
                'total_entries' => $lottery->entries()->count(),
                'winners' => $lottery->entries()->where('status', LotteryEntry::STATUS_WON)->count(),
                'approved' => $lottery->entries()->where('status', LotteryEntry::STATUS_APPROVED)->count(),
                'rejected' => $lottery->entries()->where('status', LotteryEntry::STATUS_REJECTED)->count(),
                'waitlist' => $lottery->entries()->where('status', LotteryEntry::STATUS_WAITLIST)->count(),
                'pending' => $lottery->entries()->where('status', LotteryEntry::STATUS_PENDING)->count(),
            ];
        });

        // شرکت‌کنندگان با pagination - بدون eager loading entries در lottery
        $entries = $lottery->entries()
            ->with(['personnel', 'province'])
            ->orderBy('rank')
            ->paginate(20);

        return view('lotteries.show', compact('lottery', 'stats', 'entries'));
    }

    /**
     * نمایش فرم ویرایش قرعه‌کشی
     */
    public function edit(Lottery $lottery)
    {
        $this->authorize('update', $lottery);

        $lottery->load('period.center');

        return view('lotteries.edit', compact('lottery'));
    }

    /**
     * بروزرسانی قرعه‌کشی
     */
    public function update(Request $request, Lottery $lottery)
    {
        $this->authorize('update', $lottery);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'jalali_registration_start' => 'required|string|size:10',
            'jalali_registration_end' => 'required|string|size:10',
            'jalali_draw_date' => 'required|string|size:10',
            'algorithm' => 'required|in:weighted_random,priority_based',
            'status' => 'required|in:draft,open,closed',
        ]);

        // تبدیل تاریخ‌ها
        $registrationStart = $this->jalaliToCarbon($validated['jalali_registration_start']);
        $registrationEnd = $this->jalaliToCarbon($validated['jalali_registration_end'])->endOfDay();
        $drawDate = $this->jalaliToCarbon($validated['jalali_draw_date']);

        $lottery->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'registration_start_date' => $registrationStart,
            'registration_end_date' => $registrationEnd,
            'draw_date' => $drawDate,
            'algorithm' => $validated['algorithm'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('lotteries.show', $lottery)
            ->with('success', 'قرعه‌کشی با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف قرعه‌کشی
     */
    public function destroy(Lottery $lottery)
    {
        $this->authorize('delete', $lottery);

        // بررسی شرکت‌کنندگان
        if ($lottery->entries()->count() > 0) {
            return back()->with('error', 'این قرعه‌کشی دارای شرکت‌کننده است و قابل حذف نیست.');
        }

        $lottery->delete();

        return redirect()->route('lotteries.index')
            ->with('success', 'قرعه‌کشی با موفقیت حذف شد.');
    }

    /**
     * تغییر وضعیت قرعه‌کشی
     */
    public function changeStatus(Lottery $lottery, Request $request)
    {
        $this->authorize('update', $lottery);

        $newStatus = $request->status;

        if (!in_array($newStatus, ['draft', 'open', 'closed'])) {
            return back()->with('error', 'وضعیت نامعتبر است.');
        }

        // اعتبارسنجی انتقال وضعیت
        if ($newStatus === 'open' && $lottery->status !== 'draft') {
            return back()->with('error', 'فقط قرعه‌کشی پیش‌نویس می‌تواند باز شود.');
        }

        if ($newStatus === 'closed' && $lottery->status !== 'open') {
            return back()->with('error', 'فقط قرعه‌کشی باز می‌تواند بسته شود.');
        }

        $lottery->update(['status' => $newStatus]);

        $statusLabels = [
            'draft' => 'پیش‌نویس',
            'open' => 'باز',
            'closed' => 'بسته',
        ];

        return back()->with('success', "وضعیت قرعه‌کشی به «{$statusLabels[$newStatus]}» تغییر یافت.");
    }

    /**
     * اجرای قرعه‌کشی
     */
    public function draw(Lottery $lottery)
    {
        $this->authorize('draw', $lottery);

        try {
            $result = $this->lotteryService->draw($lottery);

            return redirect()->route('lotteries.show', $lottery)
                ->with('success', "قرعه‌کشی با موفقیت انجام شد. تعداد برندگان: {$result['winners_count']}");
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در اجرای قرعه‌کشی: ' . $e->getMessage());
        }
    }

    /**
     * تایید شرکت‌کننده
     */
    public function approveEntry(Lottery $lottery, LotteryEntry $entry)
    {
        $this->authorize('manageEntries', $lottery);

        // بررسی مدیر استانی
        $user = auth()->user();
        if ($user->hasRole('provincial_admin') && !$user->canManageProvince($entry->province_id)) {
            return back()->with('error', 'شما دسترسی به این شرکت‌کننده را ندارید.');
        }

        if ($entry->status !== LotteryEntry::STATUS_WON) {
            return back()->with('error', 'فقط برندگان قابل تایید هستند.');
        }

        $entry->approve($user->id);

        return back()->with('success', 'شرکت‌کننده با موفقیت تایید شد.');
    }

    /**
     * رد شرکت‌کننده
     */
    public function rejectEntry(Lottery $lottery, LotteryEntry $entry, Request $request)
    {
        $this->authorize('manageEntries', $lottery);

        // بررسی مدیر استانی
        $user = auth()->user();
        if ($user->hasRole('provincial_admin') && !$user->canManageProvince($entry->province_id)) {
            return back()->with('error', 'شما دسترسی به این شرکت‌کننده را ندارید.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'دلیل رد الزامی است.',
        ]);

        if ($entry->status !== LotteryEntry::STATUS_WON) {
            return back()->with('error', 'فقط برندگان قابل رد هستند.');
        }

        $entry->reject($user->id, $validated['rejection_reason']);

        // ارتقا از لیست انتظار
        $this->lotteryService->promoteFromWaitlist($entry);

        return back()->with('success', 'شرکت‌کننده رد شد و نفر بعدی از لیست انتظار ارتقا یافت.');
    }

    /**
     * دریافت دوره‌های یک مرکز (AJAX)
     */
    public function getPeriods(Center $center)
    {
        $periods = Period::where('center_id', $center->id)
            ->whereIn('status', ['draft', 'open'])
            ->whereDoesntHave('lottery')
            ->orderBy('start_date')
            ->get(['id', 'code', 'jalali_start_date', 'jalali_end_date']);

        return response()->json($periods);
    }

    /**
     * تبدیل تاریخ شمسی به Carbon
     */
    private function jalaliToCarbon(string $jalaliDate)
    {
        $parts = explode('/', $jalaliDate);
        if (count($parts) !== 3) {
            return now();
        }

        $jalalian = new Jalalian((int)$parts[0], (int)$parts[1], (int)$parts[2]);
        return $jalalian->toCarbon();
    }
}
