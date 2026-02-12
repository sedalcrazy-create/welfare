<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterPersonnelRequest extends FormRequest
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
            'bale_user_id' => 'nullable|string|max:100',
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
            'full_name.required' => 'نام کامل الزامی است',
            'national_code.required' => 'کد ملی الزامی است',
            'national_code.size' => 'کد ملی باید 10 رقم باشد',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است',
            'phone.required' => 'شماره تماس الزامی است',
            'preferred_center_id.required' => 'انتخاب مرکز الزامی است',
            'preferred_center_id.exists' => 'مرکز انتخاب شده معتبر نیست',
            'preferred_period_id.required' => 'انتخاب دوره الزامی است',
            'preferred_period_id.exists' => 'دوره انتخاب شده معتبر نیست',
            'family_members.max' => 'حداکثر 10 همراه می‌توانید اضافه کنید',
            'family_members.*.full_name.required' => 'نام همراه الزامی است',
            'family_members.*.relation.required' => 'نسبت همراه الزامی است',
            'family_members.*.relation.in' => 'نسبت وارد شده معتبر نیست',
            'family_members.*.national_code.required' => 'کد ملی همراه الزامی است',
            'family_members.*.national_code.size' => 'کد ملی همراه باید 10 رقم باشد',
            'family_members.*.gender.required' => 'جنسیت همراه الزامی است',
            'family_members.*.gender.in' => 'جنسیت وارد شده معتبر نیست',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'خطا در اعتبارسنجی اطلاعات',
            'errors' => $validator->errors()
        ], 422));
    }
}
