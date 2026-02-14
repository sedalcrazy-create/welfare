<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PersonnelGuestController extends Controller
{
    /**
     * لیست همراهان پرسنل یک پرسنل
     */
    public function index(Personnel $personnel)
    {
        $personnelGuests = $personnel->personnelGuests()
            ->with('province')
            ->get()
            ->map(function ($guest) {
                return [
                    'id' => $guest->id,
                    'employee_code' => $guest->employee_code,
                    'national_code' => $guest->national_code,
                    'full_name' => $guest->full_name,
                    'phone' => $guest->phone,
                    'relation' => $guest->pivot->relation,
                    'notes' => $guest->pivot->notes,
                    'province' => $guest->province?->name,
                    'added_at' => $guest->pivot->created_at?->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $personnelGuests,
        ]);
    }

    /**
     * جستجوی پرسنل برای اضافه کردن
     */
    public function search(Personnel $personnel, Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $query = $request->input('query');

        // جستجو در کد پرسنلی، کد ملی، و نام
        $results = Personnel::query()
            ->where('id', '!=', $personnel->id) // خودش نباشد
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('employee_code', 'LIKE', "%{$query}%")
                    ->orWhere('national_code', 'LIKE', "%{$query}%")
                    ->orWhere('full_name', 'LIKE', "%{$query}%");
            })
            // پرسنل‌هایی که قبلاً اضافه نشده‌اند
            ->whereNotIn('id', function ($subQuery) use ($personnel) {
                $subQuery->select('guest_personnel_id')
                    ->from('personnel_personnel_guests')
                    ->where('personnel_id', $personnel->id);
            })
            ->limit(10)
            ->get(['id', 'employee_code', 'national_code', 'full_name', 'phone', 'province_id'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'employee_code' => $p->employee_code,
                    'national_code' => $p->national_code,
                    'full_name' => $p->full_name,
                    'phone' => $p->phone,
                    'label' => "{$p->full_name} (کد پرسنلی: {$p->employee_code})",
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * افزودن همراه پرسنل
     */
    public function store(Request $request, Personnel $personnel)
    {
        $request->validate([
            'guest_personnel_id' => [
                'required',
                'integer',
                'exists:personnel,id',
                Rule::notIn([$personnel->id]), // نمی‌تواند خودش را اضافه کند
            ],
            'relation' => [
                'required',
                'string',
                Rule::in([
                    Personnel::RELATION_SPOUSE,
                    Personnel::RELATION_CHILD,
                    Personnel::RELATION_FATHER,
                    Personnel::RELATION_MOTHER,
                    Personnel::RELATION_FATHER_IN_LAW,
                    Personnel::RELATION_MOTHER_IN_LAW,
                    Personnel::RELATION_FRIEND,
                    Personnel::RELATION_RELATIVE,
                    Personnel::RELATION_OTHER,
                ]),
            ],
            'notes' => 'nullable|string|max:500',
        ]);

        $guestPersonnelId = $request->input('guest_personnel_id');

        // بررسی اینکه قبلاً اضافه نشده باشد
        $exists = DB::table('personnel_personnel_guests')
            ->where('personnel_id', $personnel->id)
            ->where('guest_personnel_id', $guestPersonnelId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'این پرسنل قبلاً به عنوان همراه اضافه شده است.',
            ], 422);
        }

        // اضافه کردن
        $personnel->personnelGuests()->attach($guestPersonnelId, [
            'relation' => $request->input('relation'),
            'notes' => $request->input('notes'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // بازگشت اطلاعات همراه اضافه شده
        $guestPersonnel = Personnel::with('province')->find($guestPersonnelId);

        return response()->json([
            'success' => true,
            'message' => 'همراه پرسنل با موفقیت اضافه شد.',
            'data' => [
                'id' => $guestPersonnel->id,
                'employee_code' => $guestPersonnel->employee_code,
                'national_code' => $guestPersonnel->national_code,
                'full_name' => $guestPersonnel->full_name,
                'phone' => $guestPersonnel->phone,
                'relation' => $request->input('relation'),
                'notes' => $request->input('notes'),
                'province' => $guestPersonnel->province?->name,
                'added_at' => now()->format('Y-m-d H:i'),
            ],
        ], 201);
    }

    /**
     * نمایش جزئیات یک همراه پرسنل
     */
    public function show(Personnel $personnel, Personnel $guestPersonnel)
    {
        // بررسی اینکه این پرسنل واقعاً همراه پرسنل اصلی است
        $pivot = DB::table('personnel_personnel_guests')
            ->where('personnel_id', $personnel->id)
            ->where('guest_personnel_id', $guestPersonnel->id)
            ->first();

        if (!$pivot) {
            return response()->json([
                'success' => false,
                'message' => 'این همراه پرسنل یافت نشد.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $guestPersonnel->id,
                'employee_code' => $guestPersonnel->employee_code,
                'national_code' => $guestPersonnel->national_code,
                'full_name' => $guestPersonnel->full_name,
                'phone' => $guestPersonnel->phone,
                'email' => $guestPersonnel->email,
                'relation' => $pivot->relation,
                'notes' => $pivot->notes,
                'province' => $guestPersonnel->province?->name,
                'added_at' => $pivot->created_at,
            ],
        ]);
    }

    /**
     * ویرایش یادداشت همراه پرسنل
     */
    public function update(Request $request, Personnel $personnel, Personnel $guestPersonnel)
    {
        $request->validate([
            'relation' => [
                'sometimes',
                'string',
                Rule::in([
                    Personnel::RELATION_SPOUSE,
                    Personnel::RELATION_CHILD,
                    Personnel::RELATION_FATHER,
                    Personnel::RELATION_MOTHER,
                    Personnel::RELATION_FATHER_IN_LAW,
                    Personnel::RELATION_MOTHER_IN_LAW,
                    Personnel::RELATION_FRIEND,
                    Personnel::RELATION_RELATIVE,
                    Personnel::RELATION_OTHER,
                ]),
            ],
            'notes' => 'nullable|string|max:500',
        ]);

        $updated = DB::table('personnel_personnel_guests')
            ->where('personnel_id', $personnel->id)
            ->where('guest_personnel_id', $guestPersonnel->id)
            ->update([
                'relation' => $request->input('relation'),
                'notes' => $request->input('notes'),
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'همراه پرسنل یافت نشد.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'اطلاعات همراه پرسنل با موفقیت به‌روزرسانی شد.',
        ]);
    }

    /**
     * حذف همراه پرسنل
     */
    public function destroy(Personnel $personnel, Personnel $guestPersonnel)
    {
        $deleted = DB::table('personnel_personnel_guests')
            ->where('personnel_id', $personnel->id)
            ->where('guest_personnel_id', $guestPersonnel->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'همراه پرسنل یافت نشد.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'همراه پرسنل با موفقیت حذف شد.',
        ]);
    }
}
