<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_code' => 'required|string|max:20',
            'full_name' => 'required|string|max:255',
            'national_code' => 'required|string|size:10|unique:personnel,national_code',
            'phone' => 'required|string|max:20',
            'preferred_center_id' => 'required|exists:centers,id',
            'preferred_period_id' => 'required|exists:periods,id',
            'province_id' => 'nullable|exists:provinces,id',
            'notes' => 'nullable|string|max:1000',
            'family_members' => 'nullable|array|max:10',
            'family_members.*.full_name' => 'required|string|max:255',
            'family_members.*.relation' => [
                'required',
                'string',
                Rule::in(['همسر', 'فرزند', 'پدر', 'مادر', 'سایر'])
            ],
            'family_members.*.national_code' => 'required|string|size:10',
            'family_members.*.birth_date' => 'nullable|string|max:10',
            'family_members.*.gender' => 'required|in:male,female',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.required' => 'کد پرسنلی الزامی است',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است',
            'preferred_center_id.required' => 'انتخاب مرکز الزامی است',
            'preferred_period_id.required' => 'انتخاب دوره الزامی است',
        ];
    }
}
