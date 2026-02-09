<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\IntroductionLetter;
use App\Models\Lottery;
use App\Models\Personnel;
use App\Models\Province;
use App\Models\Reservation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Phase 1 Statistics
        $stats = [
            'pending_requests' => Personnel::where('status', 'pending')->count(),
            'approved_requests' => Personnel::where('status', 'approved')->count(),
            'active_letters' => IntroductionLetter::where('status', 'active')->count(),
            'total_letters' => IntroductionLetter::count(),
        ];

        // Recent Personnel Requests (last 5)
        $recentRequests = Personnel::with(['preferredCenter', 'province'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Introduction Letters (last 5)
        $recentLetters = IntroductionLetter::with(['personnel', 'center', 'issuedBy'])
            ->orderBy('issued_at', 'desc')
            ->limit(5)
            ->get();

        // Centers with unit and bed counts
        $centers = Center::withCount(['units' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        return view('dashboard', compact('stats', 'recentRequests', 'recentLetters', 'centers'));
    }
}
