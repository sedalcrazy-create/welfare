<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\Center;
use App\Models\Period;
use App\Models\Province;
use App\Services\PersonnelRequestService;
use App\Http\Requests\StorePersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Http\Requests\RejectPersonnelRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PersonnelRequestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private PersonnelRequestService $personnelService
    ) {}
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
        $this->authorize('create', Personnel::class);

        $centers = Center::where('is_active', true)->orderBy('name')->get();
        $provinces = Province::where('is_active', true)->orderBy('name')->get();
        $periods = Period::where('status', 'open')
            ->where('end_date', '>=', now())
            ->orderBy('start_date')
            ->get();

        return view('personnel-requests.create', compact('centers', 'provinces', 'periods'));
    }

    /**
     * Store a newly created request
     */
    public function store(StorePersonnelRequest $request)
    {
        $this->authorize('create', Personnel::class);

        try {
            $data = $request->validated();
            $data['registration_source'] = Personnel::SOURCE_WEB;

            // Note: We don't use service here to avoid quota check at registration
            // Quota is checked when issuing introduction letter
            $personnel = Personnel::create([
                'employee_code' => $data['employee_code'],
                'full_name' => $data['full_name'],
                'national_code' => $data['national_code'],
                'phone' => $data['phone'],
                'preferred_center_id' => $data['preferred_center_id'],
                'preferred_period_id' => $data['preferred_period_id'],
                'province_id' => $data['province_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'family_members' => $data['family_members'] ?? null,
                'registration_source' => Personnel::SOURCE_WEB,
                'status' => Personnel::STATUS_PENDING,
                'tracking_code' => Personnel::generateTrackingCode(),
            ]);

            return redirect()
                ->route('personnel-requests.show', $personnel)
                ->with('success', 'درخواست با موفقیت ثبت شد. کد پیگیری: ' . $personnel->tracking_code);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در ثبت درخواست: ' . $e->getMessage())
                ->withInput();
        }
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
        $this->authorize('update', $personnelRequest);

        // Only allow editing pending requests
        if ($personnelRequest->status !== Personnel::STATUS_PENDING) {
            return redirect()
                ->route('personnel-requests.show', $personnelRequest)
                ->with('error', 'فقط درخواست‌های در حال بررسی قابل ویرایش هستند');
        }

        $centers = Center::where('is_active', true)->orderBy('name')->get();
        $provinces = Province::where('is_active', true)->orderBy('name')->get();
        $periods = Period::where('status', 'open')
            ->where('end_date', '>=', now())
            ->orderBy('start_date')
            ->get();

        return view('personnel-requests.edit', compact('personnelRequest', 'centers', 'provinces', 'periods'));
    }

    /**
     * Update the specified request
     */
    public function update(UpdatePersonnelRequest $request, Personnel $personnelRequest)
    {
        $this->authorize('update', $personnelRequest);

        // Only allow updating pending requests
        if ($personnelRequest->status !== Personnel::STATUS_PENDING) {
            return redirect()
                ->route('personnel-requests.show', $personnelRequest)
                ->with('error', 'فقط درخواست‌های در حال بررسی قابل ویرایش هستند');
        }

        try {
            $this->personnelService->updateRequest($personnelRequest, $request->validated());

            return redirect()
                ->route('personnel-requests.show', $personnelRequest)
                ->with('success', 'درخواست با موفقیت ویرایش شد');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در ویرایش درخواست: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Approve a request
     */
    public function approve(Personnel $personnelRequest)
    {
        $this->authorize('approve', $personnelRequest);

        try {
            $this->personnelService->approve($personnelRequest, auth()->user());

            return redirect()
                ->route('introduction-letters.create', ['personnel_id' => $personnelRequest->id])
                ->with('success', 'درخواست تأیید شد. اکنون می‌توانید معرفی‌نامه صادر کنید');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در تأیید درخواست: ' . $e->getMessage());
        }
    }

    /**
     * Reject a request
     */
    public function reject(RejectPersonnelRequest $request, Personnel $personnelRequest)
    {
        $this->authorize('reject', $personnelRequest);

        try {
            $this->personnelService->reject(
                $personnelRequest,
                auth()->user(),
                $request->rejection_reason
            );

            return redirect()
                ->route('personnel-requests.index')
                ->with('success', 'درخواست رد شد');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در رد درخواست: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified request
     */
    public function destroy(Personnel $personnelRequest)
    {
        $this->authorize('delete', $personnelRequest);

        try {
            $this->personnelService->deleteRequest($personnelRequest);

            return redirect()
                ->route('personnel-requests.index')
                ->with('success', 'درخواست حذف شد');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در حذف درخواست: ' . $e->getMessage());
        }
    }
}
