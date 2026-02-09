<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Center;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Reservation::class);

        // استفاده از select برای کاهش داده‌ها
        $query = Reservation::with([
            'personnel:id,full_name,employee_code,province_id',
            'unit:id,number,type,center_id',
            'unit.center:id,name',
            'period:id,code,jalali_start_date,jalali_end_date'
        ]);

        // فیلتر مرکز
        if ($request->filled('center_id')) {
            $query->whereHas('unit', fn($q) => $q->where('center_id', $request->center_id));
        }

        // فیلتر وضعیت
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فیلتر تاریخ
        if ($request->filled('date_from')) {
            $query->whereHas('period', fn($q) => $q->where('start_date', '>=', $request->date_from));
        }

        // جستجو
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reservation_code', 'like', "%{$search}%")
                  ->orWhereHas('personnel', fn($pq) => $pq->where('full_name', 'like', "%{$search}%"));
            });
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(20);

        // Cache لیست مراکز
        $centers = Cache::remember('active_centers', 600, function () {
            return Center::where('is_active', true)->orderBy('name')->get();
        });

        return view('reservations.index', compact('reservations', 'centers'));
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        $reservation->load(['personnel.province', 'unit.center', 'period', 'lotteryEntry']);

        return view('reservations.show', compact('reservation'));
    }

    public function checkIn(Reservation $reservation)
    {
        $this->authorize('checkIn', $reservation);

        $reservation->checkIn(auth()->id());

        return back()->with('success', 'ورود با موفقیت ثبت شد.');
    }

    public function checkOut(Reservation $reservation)
    {
        $this->authorize('checkOut', $reservation);

        $reservation->checkOut(auth()->id());

        // ثبت سابقه استفاده
        $reservation->personnel->usageHistories()->create([
            'center_id' => $reservation->unit->center_id,
            'reservation_id' => $reservation->id,
            'check_in_date' => $reservation->check_in_at,
            'check_out_date' => now(),
            'tariff_type' => $reservation->tariff_type,
        ]);

        return back()->with('success', 'خروج با موفقیت ثبت شد.');
    }

    public function cancel(Reservation $reservation, Request $request)
    {
        $this->authorize('cancel', $reservation);

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ], [
            'cancellation_reason.required' => 'دلیل لغو الزامی است.',
        ]);

        $reservation->cancel($validated['cancellation_reason']);

        return back()->with('success', 'رزرو با موفقیت لغو شد.');
    }

    public function printVoucher(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        $reservation->load(['personnel.province', 'unit.center', 'period']);

        return view('reservations.voucher', compact('reservation'));
    }
}
