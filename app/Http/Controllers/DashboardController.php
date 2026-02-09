<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Lottery;
use App\Models\Personnel;
use App\Models\Province;
use App\Models\Reservation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_centers' => Center::where('is_active', true)->count(),
            'total_beds' => Center::where('is_active', true)->sum('bed_count'),
            'total_personnel' => Personnel::where('is_active', true)->count(),
            'total_provinces' => Province::where('is_active', true)->count(),
            'active_lotteries' => Lottery::whereIn('status', ['open', 'closed'])->count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'today_check_ins' => Reservation::where('status', 'confirmed')
                ->whereDate('created_at', today())
                ->count(),
        ];

        $recentLotteries = Lottery::with(['period.center'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentReservations = Reservation::with(['personnel', 'unit.center', 'period'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $centers = Center::withCount(['units' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        return view('dashboard', compact('stats', 'recentLotteries', 'recentReservations', 'centers'));
    }
}
