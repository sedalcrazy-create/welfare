<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('personnel'));
    }

    public function rules(): array
    {
        $personnelId = $this->route('personnel')->id;

        return [
            'province_id' => 'required|exists:provinces,id',
            'employee_code' => ['required', 'string', 'max:20', Rule::unique('personnel')->ignore($personnelId)],
            'national_code' => ['required', 'string', 'size:10', Rule::unique('personnel')->ignore($personnelId)],
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
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.required' => 'کد پرسنلی الزامی است.',
            'employee_code.unique' => 'این کد پرسنلی قبلاً ثبت شده است.',
            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
            'national_code.size' => 'کد ملی باید 10 رقم باشد.',
            'full_name.required' => 'نام کامل الزامی است.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_isargar' => $this->boolean('is_isargar'),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
