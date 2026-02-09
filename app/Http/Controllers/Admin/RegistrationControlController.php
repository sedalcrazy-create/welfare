<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Period;
use App\Models\RegistrationControl;
use Illuminate\Http\Request;

class RegistrationControlController extends Controller
{
    public function index()
    {
        $controls = RegistrationControl::with(['center', 'period', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $centers = Center::orderBy('name')->get();
        $periods = Period::with('center')->orderBy('start_date', 'desc')->get();

        return view('admin.registration-control.index', compact('controls', 'centers', 'periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rule_type' => 'required|in:global,date_range,center,period',
            'allow_registration' => 'required|boolean',
            'message' => 'nullable|string|max:500',
            'start_date' => 'required_if:rule_type,date_range|nullable|string',
            'end_date' => 'required_if:rule_type,date_range|nullable|string',
            'center_id' => 'required_if:rule_type,center|nullable|exists:centers,id',
            'period_id' => 'required_if:rule_type,period|nullable|exists:periods,id',
        ]);

        RegistrationControl::create([
            'rule_type' => $request->rule_type,
            'is_active' => true,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'center_id' => $request->center_id,
            'period_id' => $request->period_id,
            'allow_registration' => $request->allow_registration,
            'message' => $request->message,
            'created_by_user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.registration-control.index')
            ->with('success', 'قانون جدید ایجاد شد');
    }

    public function update(Request $request, RegistrationControl $registrationControl)
    {
        $request->validate([
            'allow_registration' => 'required|boolean',
            'message' => 'nullable|string|max:500',
            'start_date' => 'required_if:rule_type,date_range|nullable|string',
            'end_date' => 'required_if:rule_type,date_range|nullable|string',
        ]);

        $registrationControl->update($request->only([
            'allow_registration',
            'message',
            'start_date',
            'end_date',
        ]));

        return redirect()
            ->route('admin.registration-control.index')
            ->with('success', 'قانون به‌روزرسانی شد');
    }

    public function toggleStatus(RegistrationControl $registrationControl)
    {
        $registrationControl->update([
            'is_active' => !$registrationControl->is_active,
        ]);

        $status = $registrationControl->is_active ? 'فعال' : 'غیرفعال';

        return redirect()
            ->route('admin.registration-control.index')
            ->with('success', "قانون {$status} شد");
    }

    public function destroy(RegistrationControl $registrationControl)
    {
        $registrationControl->delete();

        return redirect()
            ->route('admin.registration-control.index')
            ->with('success', 'قانون حذف شد');
    }
}
