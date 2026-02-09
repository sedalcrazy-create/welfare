<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('center'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('centers')->ignore($this->route('center')->id)],
            'city' => 'required|string|max:255',
            'type' => 'required|in:religious,beach,mountain',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'stay_duration' => 'required|integer|min:1|max:30',
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i',
            'amenities' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام مرکز الزامی است.',
            'name.unique' => 'این نام قبلاً ثبت شده است.',
            'city.required' => 'شهر الزامی است.',
            'type.required' => 'نوع مرکز الزامی است.',
            'stay_duration.required' => 'مدت اقامت الزامی است.',
            'stay_duration.min' => 'مدت اقامت باید حداقل ۱ شب باشد.',
            'check_in_time.required' => 'ساعت ورود الزامی است.',
            'check_out_time.required' => 'ساعت خروج الزامی است.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
