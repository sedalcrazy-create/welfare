<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottery;
use App\Models\LotteryEntry;
use App\Services\PriorityScoreService;
use Illuminate\Http\Request;

class LotteryController extends Controller
{
    public function __construct(
        private PriorityScoreService $priorityScoreService
    ) {}

    public function index(Request $request)
    {
        $lotteries = Lottery::with(['period.center', 'period.season'])
            ->whereIn('status', ['open', 'closed', 'drawn', 'completed'])
            ->orderBy('draw_date', 'desc')
            ->paginate(10);

        return response()->json($lotteries);
    }

    public function active()
    {
        $lotteries = Lottery::with(['period.center', 'period.season'])
            ->where('status', 'open')
            ->where('registration_end_date', '>=', now())
            ->orderBy('registration_end_date')
            ->get();

        return response()->json($lotteries);
    }

    public function show(Lottery $lottery)
    {
        $lottery->load(['period.center', 'period.season']);

        $user = auth()->user();
        $myEntry = null;

        if ($user->personnel_id) {
            $myEntry = LotteryEntry::where('lottery_id', $lottery->id)
                ->where('personnel_id', $user->personnel_id)
                ->first();
        }

        return response()->json([
            'lottery' => $lottery,
            'my_entry' => $myEntry,
            'can_enter' => $lottery->canRegister() && !$myEntry,
        ]);
    }

    public function enter(Request $request, Lottery $lottery)
    {
        $user = auth()->user();
        $personnel = $user->personnel;

        if (!$personnel) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات پرسنلی یافت نشد.',
            ], 400);
        }

        if (!$lottery->canRegister()) {
            return response()->json([
                'success' => false,
                'message' => 'ثبت‌نام در این قرعه‌کشی امکان‌پذیر نیست.',
            ], 400);
        }

        // Check if already entered
        $existingEntry = LotteryEntry::where('lottery_id', $lottery->id)
            ->where('personnel_id', $personnel->id)
            ->first();

        if ($existingEntry) {
            return response()->json([
                'success' => false,
                'message' => 'شما قبلاً در این قرعه‌کشی ثبت‌نام کرده‌اید.',
            ], 400);
        }

        $request->validate([
            'family_count' => 'required|integer|min:1|max:10',
            'guests' => 'nullable|array',
            'guests.*.name' => 'required|string',
            'guests.*.relation' => 'required|string',
            'guests.*.national_code' => 'nullable|string|size:10',
        ]);

        $entry = LotteryEntry::create([
            'lottery_id' => $lottery->id,
            'personnel_id' => $personnel->id,
            'province_id' => $personnel->province_id,
            'family_count' => $request->family_count,
            'guests' => $request->guests,
            'status' => LotteryEntry::STATUS_PENDING,
        ]);

        // Calculate priority score
        $score = $this->priorityScoreService->calculate($entry);
        $entry->update(['priority_score' => $score]);

        return response()->json([
            'success' => true,
            'message' => 'ثبت‌نام شما با موفقیت انجام شد.',
            'entry' => $entry,
        ]);
    }

    public function cancelEntry(Lottery $lottery)
    {
        $user = auth()->user();

        $entry = LotteryEntry::where('lottery_id', $lottery->id)
            ->where('personnel_id', $user->personnel_id)
            ->first();

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'شما در این قرعه‌کشی ثبت‌نام نکرده‌اید.',
            ], 404);
        }

        if (!in_array($entry->status, [LotteryEntry::STATUS_PENDING])) {
            return response()->json([
                'success' => false,
                'message' => 'امکان لغو ثبت‌نام وجود ندارد.',
            ], 400);
        }

        $entry->update(['status' => LotteryEntry::STATUS_CANCELLED]);

        return response()->json([
            'success' => true,
            'message' => 'ثبت‌نام شما لغو شد.',
        ]);
    }

    public function myEntry(Lottery $lottery)
    {
        $user = auth()->user();

        $entry = LotteryEntry::where('lottery_id', $lottery->id)
            ->where('personnel_id', $user->personnel_id)
            ->with('reservation')
            ->first();

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'شما در این قرعه‌کشی ثبت‌نام نکرده‌اید.',
            ], 404);
        }

        $scoreBreakdown = $this->priorityScoreService->getScoreBreakdown($entry);

        return response()->json([
            'entry' => $entry,
            'score_breakdown' => $scoreBreakdown,
        ]);
    }

    public function results(Lottery $lottery)
    {
        if (!in_array($lottery->status, [Lottery::STATUS_DRAWN, Lottery::STATUS_APPROVAL, Lottery::STATUS_COMPLETED])) {
            return response()->json([
                'success' => false,
                'message' => 'نتایج هنوز اعلام نشده است.',
            ], 400);
        }

        $winners = LotteryEntry::where('lottery_id', $lottery->id)
            ->whereIn('status', [LotteryEntry::STATUS_WON, LotteryEntry::STATUS_APPROVED])
            ->with(['personnel:id,full_name,employee_code', 'province:id,name'])
            ->orderBy('rank')
            ->get()
            ->map(function ($entry) {
                return [
                    'rank' => $entry->rank,
                    'name' => $entry->personnel->full_name,
                    'province' => $entry->province->name,
                    'status' => $entry->status,
                ];
            });

        return response()->json([
            'lottery' => $lottery->only(['id', 'title', 'status', 'drawn_at', 'total_participants', 'total_winners']),
            'winners' => $winners,
        ]);
    }
}
