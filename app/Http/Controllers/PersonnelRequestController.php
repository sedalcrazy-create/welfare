<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\Center;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PersonnelRequestController extends Controller
{
    /**
     * Display a listing of personnel requests
     */
    public function index(Request $request)
    {
        $query = Personnel::with(['preferredCenter', 'province']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: show pending requests
            $query->where('status', Personnel::STATUS_PENDING);
        }

        // Filter by registration source
        if ($request->filled('source')) {
            $query->where('registration_source', $request->source);
        }

        // Filter by center
        if ($request->filled('center_id')) {
            $query->where('preferred_center_id', $request->center_id);
        }

        // Filter by province (for provincial admins)
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('national_code', 'like', "%{$search}%")
                    ->orWhere('tracking_code', 'like', "%{$search}%");
            });
        }

        // Check user role
        $user = auth()->user();
        if ($user->isProvincialAdmin() && $user->province_id) {
            // Provincial admins only see their province's requests
            $query->where('province_id', $user->province_id);
        }

        $requests = $query->latest()->paginate(20);

        $centers = Center::where('is_active', true)->get();
        $provinces = Province::where('is_active', true)->get();

        return view('personnel-requests.index', compact('requests', 'centers', 'provinces'));
    }

    /**
     * Show the form for creating a new request
     */
    public function create()
    {
        $centers = Center::where('is_active', true)->get();
        $provinces = Province::where('is_active', true)->get();

        return view('personnel-requests.create', compact('centers', 'provinces'));
    }

    /**
     * Store a newly created request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:20',
            'full_name' => 'required|string|max:255',
            'national_code' => 'required|string|size:10|unique:personnel,national_code',
            'phone' => 'required|string|max:20',
            'preferred_center_id' => 'required|exists:centers,id',
            'province_id' => 'nullable|exists:provinces,id',
            'notes' => 'nullable|string|max:1000',

            // همراهان
            'family_members' => 'nullable|array|max:10',
            'family_members.*.full_name' => 'required|string|max:255',
            'family_members.*.relation' => 'required|string|in:همسر,فرزند,پدر,مادر,سایر',
            'family_members.*.national_code' => 'required|string|size:10',
            'family_members.*.birth_date' => 'nullable|string|max:10',
            'family_members.*.gender' => 'required|in:male,female',
        ], [
            'employee_code.required' => 'کد پرسنلی الزامی است',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است',
            'family_members.*.national_code.size' => 'کد ملی همراه باید 10 رقم باشد',
            'family_members.*.relation.in' => 'نسبت وارد شده معتبر نیست',
        ]);

        $personnel = Personnel::create([
            'employee_code' => $validated['employee_code'],
            'full_name' => $validated['full_name'],
            'national_code' => $validated['national_code'],
            'phone' => $validated['phone'],
            'preferred_center_id' => $validated['preferred_center_id'],
            'province_id' => $validated['province_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'family_members' => $validated['family_members'] ?? null,
            'registration_source' => Personnel::SOURCE_MANUAL,
            'status' => Personnel::STATUS_PENDING,
            'tracking_code' => Personnel::generateTrackingCode(),
        ]);

        return redirect()
            ->route('personnel-requests.show', $personnel)
            ->with('success', 'درخواست با موفقیت ثبت شد. کد پیگیری: ' . $personnel->tracking_code);
    }

    /**
     * Display the specified request
     */
    public function show(Personnel $personnelRequest)
    {
        $personnelRequest->load(['preferredCenter', 'province', 'introductionLetters.center', 'introductionLetters.issuedBy']);

        return view('personnel-requests.show', compact('personnelRequest'));
    }

    /**
     * Show the form for editing the specified request
     */
    public function edit(Personnel $personnelRequest)
    {
        // Only allow editing pending requests
        if ($personnelRequest->status !== Personnel::STATUS_PENDING) {
            return redirect()
                ->route('personnel-requests.show', $personnelRequest)
                ->with('error', 'فقط درخواست‌های در حال بررسی قابل ویرایش هستند');
        }

        $centers = Center::where('is_active', true)->get();
        $provinces = Province::where('is_active', true)->get();

        return view('personnel-requests.edit', compact('personnelRequest', 'centers', 'provinces'));
    }

    /**
     * Update the specified request
     */
    public function update(Request $request, Personnel $personnelRequest)
    {
        // Only allow updating pending requests
        if ($personnelRequest->status !== Personnel::STATUS_PENDING) {
            return redirect()
                ->route('personnel-requests.show', $personnelRequest)
                ->with('error', 'فقط درخواست‌های در حال بررسی قابل ویرایش هستند');
        }

        $validated = $request->validate([
            'employee_code' => 'required|string|max:20',
            'full_name' => 'required|string|max:255',
            'national_code' => 'required|string|size:10|unique:personnel,national_code,' . $personnelRequest->id,
            'phone' => 'required|string|max:20',
            'preferred_center_id' => 'required|exists:centers,id',
            'province_id' => 'nullable|exists:provinces,id',
            'notes' => 'nullable|string|max:1000',

            // همراهان
            'family_members' => 'nullable|array|max:10',
            'family_members.*.full_name' => 'required|string|max:255',
            'family_members.*.relation' => 'required|string|in:همسر,فرزند,پدر,مادر,سایر',
            'family_members.*.national_code' => 'required|string|size:10',
            'family_members.*.birth_date' => 'nullable|string|max:10',
            'family_members.*.gender' => 'required|in:male,female',
        ]);

        $personnelRequest->update($validated);

        return redirect()
            ->route('personnel-requests.show', $personnelRequest)
            ->with('success', 'درخواست با موفقیت ویرایش شد');
    }

    /**
     * Approve a request
     */
    public function approve(Personnel $personnelRequest)
    {
        if ($personnelRequest->status !== Personnel::STATUS_PENDING) {
            return redirect()
                ->route('personnel-requests.show', $personnelRequest)
                ->with('error', 'این درخواست قبلاً بررسی شده است');
        }

        $personnelRequest->update([
            'status' => Personnel::STATUS_APPROVED,
        ]);

        return redirect()
            ->route('personnel-requests.show', $personnelRequest)
            ->with('success', 'درخواست تأیید شد. اکنون می‌توانید معرفی‌نامه صادر کنید');
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, Personnel $personnelRequest)
    {
        if ($personnelRequest->status !== Personnel::STATUS_PENDING) {
            return redirect()
                ->route('personnel-requests.show', $personnelRequest)
                ->with('error', 'این درخواست قبلاً بررسی شده است');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $personnelRequest->update([
            'status' => Personnel::STATUS_REJECTED,
            'notes' => $validated['rejection_reason'],
        ]);

        return redirect()
            ->route('personnel-requests.index')
            ->with('success', 'درخواست رد شد');
    }

    /**
     * Remove the specified request
     */
    public function destroy(Personnel $personnelRequest)
    {
        // Only allow deleting pending or rejected requests
        if ($personnelRequest->status === Personnel::STATUS_APPROVED) {
            // Check if has introduction letters
            if ($personnelRequest->introductionLetters()->exists()) {
                return redirect()
                    ->route('personnel-requests.show', $personnelRequest)
                    ->with('error', 'نمی‌توان درخواستی که معرفی‌نامه دارد را حذف کرد');
            }
        }

        $personnelRequest->delete();

        return redirect()
            ->route('personnel-requests.index')
            ->with('success', 'درخواست حذف شد');
    }
}
