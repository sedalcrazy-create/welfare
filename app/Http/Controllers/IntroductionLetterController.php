<?php

namespace App\Http\Controllers;

use App\Models\IntroductionLetter;
use App\Models\Personnel;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntroductionLetterController extends Controller
{
    /**
     * Display a listing of introduction letters
     */
    public function index(Request $request)
    {
        $query = IntroductionLetter::with(['personnel', 'center', 'issuedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by center
        if ($request->filled('center_id')) {
            $query->where('center_id', $request->center_id);
        }

        // Filter by issued user
        $user = auth()->user();
        if ($user->isProvincialAdmin() && !$user->isAdmin()) {
            $query->where('issued_by_user_id', $user->id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('letter_code', 'like', "%{$search}%")
                    ->orWhereHas('personnel', function ($q2) use ($search) {
                        $q2->where('full_name', 'like', "%{$search}%")
                            ->orWhere('national_code', 'like', "%{$search}%");
                    });
            });
        }

        $letters = $query->latest('issued_at')->paginate(20);
        $centers = Center::where('is_active', true)->get();

        return view('introduction-letters.index', compact('letters', 'centers'));
    }

    /**
     * Show the form for creating a new letter
     */
    public function create(Request $request)
    {
        $personnelId = $request->get('personnel_id');

        if ($personnelId) {
            $personnel = Personnel::findOrFail($personnelId);

            // Check if approved
            if ($personnel->status !== Personnel::STATUS_APPROVED) {
                return redirect()
                    ->route('personnel-requests.show', $personnel)
                    ->with('error', 'ابتدا باید درخواست را تأیید کنید');
            }
        } else {
            $personnel = null;
        }

        // Check user quota
        $user = auth()->user();
        if (!$user->hasQuotaAvailable()) {
            return redirect()
                ->back()
                ->with('error', 'سهمیه شما تمام شده است');
        }

        $centers = Center::where('is_active', true)->get();
        $approvedPersonnel = Personnel::approved()->get();

        return view('introduction-letters.create', compact('personnel', 'centers', 'approvedPersonnel'));
    }

    /**
     * Store a newly created letter
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'personnel_id' => 'required|exists:personnel,id',
            'center_id' => 'required|exists:centers,id',
            'family_count' => 'required|integer|min:1|max:10',
            'notes' => 'nullable|string|max:1000',
        ]);

        $personnel = Personnel::findOrFail($validated['personnel_id']);

        // Check if approved
        if ($personnel->status !== Personnel::STATUS_APPROVED) {
            return redirect()
                ->back()
                ->with('error', 'این درخواست هنوز تأیید نشده است');
        }

        $user = auth()->user();

        // Check quota
        if (!$user->hasQuotaAvailable()) {
            return redirect()
                ->back()
                ->with('error', 'سهمیه شما تمام شده است');
        }

        $center = Center::findOrFail($validated['center_id']);

        try {
            DB::beginTransaction();

            // Generate letter code
            $letterCode = IntroductionLetter::generateLetterCode($center);

            // Create introduction letter
            $letter = IntroductionLetter::create([
                'letter_code' => $letterCode,
                'personnel_id' => $personnel->id,
                'center_id' => $center->id,
                'issued_by_user_id' => $user->id,
                'family_count' => $validated['family_count'],
                'notes' => $validated['notes'] ?? null,
                'issued_at' => now(),
                'status' => 'active',
            ]);

            // Update user quota
            $user->incrementQuotaUsed();

            DB::commit();

            return redirect()
                ->route('introduction-letters.show', $letter)
                ->with('success', 'معرفی‌نامه با موفقیت صادر شد');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'خطا در صدور معرفی‌نامه: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified letter
     */
    public function show(IntroductionLetter $introductionLetter)
    {
        $introductionLetter->load(['personnel', 'center', 'issuedBy']);

        return view('introduction-letters.show', compact('introductionLetter'));
    }

    /**
     * Cancel a letter
     */
    public function cancel(Request $request, IntroductionLetter $introductionLetter)
    {
        if (!$introductionLetter->isActive()) {
            return redirect()
                ->route('introduction-letters.show', $introductionLetter)
                ->with('error', 'فقط معرفی‌نامه‌های فعال قابل لغو هستند');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Cancel letter
            $introductionLetter->cancel($validated['cancellation_reason'], auth()->id());

            // Return quota to user
            $introductionLetter->issuedBy->decrementQuotaUsed();

            DB::commit();

            return redirect()
                ->route('introduction-letters.show', $introductionLetter)
                ->with('success', 'معرفی‌نامه لغو شد و سهمیه بازگردانده شد');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'خطا در لغو معرفی‌نامه: ' . $e->getMessage());
        }
    }

    /**
     * Mark letter as used
     */
    public function markAsUsed(IntroductionLetter $introductionLetter)
    {
        if (!$introductionLetter->isActive()) {
            return redirect()
                ->route('introduction-letters.show', $introductionLetter)
                ->with('error', 'این معرفی‌نامه قبلاً استفاده شده است');
        }

        $introductionLetter->markAsUsed();

        return redirect()
            ->route('introduction-letters.show', $introductionLetter)
            ->with('success', 'معرفی‌نامه به عنوان استفاده شده علامت‌گذاری شد');
    }

    /**
     * Print/Download letter PDF
     */
    public function print(IntroductionLetter $introductionLetter)
    {
        $introductionLetter->load(['personnel', 'center', 'issuedBy']);

        return view('introduction-letters.print', compact('introductionLetter'));
    }
}
