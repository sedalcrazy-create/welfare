<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;

class PersonnelController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Personnel::class);

        // استفاده از withCount برای جلوگیری از N+1 Query
        $query = Personnel::with('province:id,name,code')
            ->withCount(['reservations', 'lotteryEntries']);

        // فیلتر استان (مدیر استانی فقط استان خودش)
        $user = auth()->user();
        if ($user->hasRole('provincial_admin') && $user->province_id) {
            $query->where('province_id', $user->province_id);
        } elseif ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // جستجو
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('national_code', 'like', "%{$search}%");
            });
        }

        // فیلتر وضعیت
        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        // فیلتر ایثارگران
        if ($request->boolean('is_isargar')) {
            $query->where('is_isargar', true);
        }

        $personnel = $query->orderBy('full_name')->paginate(20);

        // Cache لیست استان‌ها
        $provinces = Cache::remember('active_provinces', 600, function () {
            return Province::where('is_active', true)->orderBy('name')->get();
        });

        return view('personnel.index', compact('personnel', 'provinces'));
    }

    public function create()
    {
        $this->authorize('create', Personnel::class);

        $provinces = Province::where('is_active', true)->orderBy('name')->get();

        return view('personnel.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Personnel::class);

        $validated = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'employee_code' => 'required|string|max:20|unique:personnel',
            'national_code' => 'required|string|size:10|unique:personnel',
            'full_name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'gender' => 'required|in:male,female',
            'phone' => 'nullable|string|max:11',
            'email' => 'nullable|email|max:255',
            'employment_status' => 'required|in:active,retired',
            'service_years' => 'required|integer|min:0|max:50',
            'family_count' => 'required|integer|min:1|max:15',
            'is_isargar' => 'boolean',
            'isargar_type' => 'nullable|required_if:is_isargar,true|in:veteran,freed_pow,martyr_child,martyr_spouse,martyr_parent',
            'isargar_percentage' => 'nullable|integer|min:0|max:100',
        ], [
            'employee_code.required' => 'کد پرسنلی الزامی است.',
            'employee_code.unique' => 'این کد پرسنلی قبلاً ثبت شده است.',
            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
            'national_code.size' => 'کد ملی باید 10 رقم باشد.',
            'full_name.required' => 'نام کامل الزامی است.',
        ]);

        $validated['is_isargar'] = $request->boolean('is_isargar');
        $validated['is_active'] = true;

        Personnel::create($validated);

        return redirect()->route('personnel.index')
            ->with('success', 'پرسنل جدید با موفقیت ثبت شد.');
    }

    public function show(Personnel $personnel)
    {
        $this->authorize('view', $personnel);

        $personnel->load(['province', 'lotteryEntries.lottery.period.center', 'reservations.unit.center']);

        // سابقه استفاده
        $usageHistory = $personnel->usageHistories()
            ->with('center')
            ->orderBy('check_out_date', 'desc')
            ->limit(10)
            ->get();

        return view('personnel.show', compact('personnel', 'usageHistory'));
    }

    public function edit(Personnel $personnel)
    {
        $this->authorize('update', $personnel);

        $provinces = Province::where('is_active', true)->orderBy('name')->get();

        return view('personnel.edit', compact('personnel', 'provinces'));
    }

    public function update(Request $request, Personnel $personnel)
    {
        $this->authorize('update', $personnel);

        $validated = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'employee_code' => ['required', 'string', 'max:20', Rule::unique('personnel')->ignore($personnel->id)],
            'national_code' => ['required', 'string', 'size:10', Rule::unique('personnel')->ignore($personnel->id)],
            'full_name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'gender' => 'required|in:male,female',
            'phone' => 'nullable|string|max:11',
            'email' => 'nullable|email|max:255',
            'employment_status' => 'required|in:active,retired',
            'service_years' => 'required|integer|min:0|max:50',
            'family_count' => 'required|integer|min:1|max:15',
            'is_isargar' => 'boolean',
            'isargar_type' => 'nullable|in:veteran,freed_pow,martyr_child,martyr_spouse,martyr_parent',
            'isargar_percentage' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_isargar'] = $request->boolean('is_isargar');
        $validated['is_active'] = $request->boolean('is_active', true);

        $personnel->update($validated);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'اطلاعات پرسنل با موفقیت بروزرسانی شد.');
    }

    public function destroy(Personnel $personnel)
    {
        $this->authorize('delete', $personnel);

        // بررسی رزروها
        if ($personnel->reservations()->exists()) {
            return back()->with('error', 'این پرسنل دارای رزرو است و قابل حذف نیست.');
        }

        $personnel->delete();

        return redirect()->route('personnel.index')
            ->with('success', 'پرسنل با موفقیت حذف شد.');
    }

    public function import(Request $request)
    {
        $this->authorize('import', Personnel::class);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        // TODO: Implement Excel import
        return back()->with('info', 'قابلیت ایمپورت به زودی فعال می‌شود.');
    }
}
