<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProvinceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('province'));
    }

    public function rules(): array
    {
        $provinceId = $this->route('province')->id;

        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:provinces,code,' . $provinceId,
            'personnel_count' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام استان الزامی است.',
            'code.required' => 'کد استان الزامی است.',
            'code.unique' => 'این کد قبلاً استفاده شده است.',
            'personnel_count.required' => 'تعداد پرسنل الزامی است.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
