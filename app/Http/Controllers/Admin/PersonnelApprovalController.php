<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Center;
use App\Models\Province;
use App\Services\PersonnelRequestService;
use App\Http\Requests\RejectPersonnelRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PersonnelApprovalController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private PersonnelRequestService $personnelService
    ) {}

    /**
     * Display pending personnel requests for approval
     */
    public function pending(Request $request)
    {
        $this->authorize('approve', Personnel::class);

        $query = Personnel::with(['preferredCenter', 'preferredPeriod', 'province'])
            ->where('status', Personnel::STATUS_PENDING);

        // Provincial admin filter
        $user = auth()->user();
        if ($user->hasRole('provincial_admin') && $user->province_id) {
            $query->where('province_id', $user->province_id);
        } elseif ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // Filter by center
        if ($request->filled('center_id')) {
            $query->where('preferred_center_id', $request->center_id);
        }

        // Filter by period
        if ($request->filled('period_id')) {
            $query->where('preferred_period_id', $request->period_id);
        }

        // Filter by registration source
        if ($request->filled('source')) {
            $query->where('registration_source', $request->source);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('national_code', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('tracking_code', 'like', "%{$search}%");
            });
        }

        $requests = $query->latest()->paginate(20);

        // Statistics
        $stats = [
            'total_pending' => Personnel::pending()->count(),
            'total_approved_today' => Personnel::approved()
                ->whereDate('updated_at', today())
                ->count(),
            'total_rejected_today' => Personnel::rejected()
                ->whereDate('updated_at', today())
                ->count(),
        ];

        $centers = Center::where('is_active', true)->orderBy('name')->get();
        $provinces = Province::where('is_active', true)->orderBy('name')->get();

        return view('admin.personnel-approvals.pending', compact(
            'requests',
            'stats',
            'centers',
            'provinces'
        ));
    }

    /**
     * Display detailed view for a pending request
     */
    public function show(Personnel $personnel)
    {
        $this->authorize('approve', Personnel::class);

        $personnel->load([
            'preferredCenter',
            'preferredPeriod',
            'province',
            'introductionLetters'
        ]);

        return view('admin.personnel-approvals.show', compact('personnel'));
    }

    /**
     * Approve a personnel request
     */
    public function approve(Personnel $personnel)
    {
        $this->authorize('approve', $personnel);

        try {
            $this->personnelService->approve($personnel, auth()->user());

            // Redirect to letter issuance page
            return redirect()
                ->route('introduction-letters.create', ['personnel_id' => $personnel->id])
                ->with('success', 'درخواست تأیید شد. اکنون می‌توانید معرفی‌نامه صادر کنید');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در تأیید درخواست: ' . $e->getMessage());
        }
    }

    /**
     * Reject a personnel request
     */
    public function reject(RejectPersonnelRequest $request, Personnel $personnel)
    {
        $this->authorize('reject', $personnel);

        try {
            $this->personnelService->reject(
                $personnel,
                auth()->user(),
                $request->rejection_reason
            );

            return redirect()
                ->route('admin.personnel-approvals.pending')
                ->with('success', 'درخواست رد شد');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطا در رد درخواست: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve multiple requests
     */
    public function bulkApprove(Request $request)
    {
        $this->authorize('approve', Personnel::class);

        $request->validate([
            'personnel_ids' => 'required|array|min:1',
            'personnel_ids.*' => 'required|exists:personnel,id'
        ]);

        $approvedCount = 0;
        $errors = [];

        foreach ($request->personnel_ids as $personnelId) {
            try {
                $personnel = Personnel::findOrFail($personnelId);

                if ($personnel->status === Personnel::STATUS_PENDING) {
                    $this->personnelService->approve($personnel, auth()->user());
                    $approvedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "خطا در تأیید درخواست {$personnelId}: " . $e->getMessage();
            }
        }

        if ($approvedCount > 0) {
            $message = "{$approvedCount} درخواست تأیید شد";
            if (!empty($errors)) {
                $message .= " (" . count($errors) . " خطا)";
            }
            return redirect()
                ->back()
                ->with('success', $message)
                ->with('errors', $errors);
        }

        return redirect()
            ->back()
            ->with('error', 'هیچ درخواستی تأیید نشد')
            ->with('errors', $errors);
    }

    /**
     * Bulk reject multiple requests
     */
    public function bulkReject(Request $request)
    {
        $this->authorize('reject', Personnel::class);

        $request->validate([
            'personnel_ids' => 'required|array|min:1',
            'personnel_ids.*' => 'required|exists:personnel,id',
            'rejection_reason' => 'required|string|min:10|max:500'
        ]);

        $rejectedCount = 0;
        $errors = [];

        foreach ($request->personnel_ids as $personnelId) {
            try {
                $personnel = Personnel::findOrFail($personnelId);

                if ($personnel->status === Personnel::STATUS_PENDING) {
                    $this->personnelService->reject(
                        $personnel,
                        auth()->user(),
                        $request->rejection_reason
                    );
                    $rejectedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "خطا در رد درخواست {$personnelId}: " . $e->getMessage();
            }
        }

        if ($rejectedCount > 0) {
            $message = "{$rejectedCount} درخواست رد شد";
            if (!empty($errors)) {
                $message .= " (" . count($errors) . " خطا)";
            }
            return redirect()
                ->back()
                ->with('success', $message)
                ->with('errors', $errors);
        }

        return redirect()
            ->back()
            ->with('error', 'هیچ درخواستی رد نشد')
            ->with('errors', $errors);
    }
}
