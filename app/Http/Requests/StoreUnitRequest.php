<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Unit::class);
    }

    public function rules(): array
    {
        return [
            'center_id' => 'required|exists:centers,id',
            'number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units')->where(function ($query) {
                    return $query->where('center_id', $this->center_id);
                }),
            ],
            'name' => 'nullable|string|max:255',
            'type' => 'required|in:room,suite,villa,apartment',
            'bed_count' => 'required|integer|min:1|max:20',
            'floor' => 'nullable|integer|min:-2|max:50',
            'block' => 'nullable|string|max:10',
            'amenities' => 'nullable|array',
            'is_management' => 'boolean',
            'status' => 'required|in:active,maintenance,blocked',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'center_id.required' => 'انتخاب مرکز الزامی است.',
            'number.required' => 'شماره واحد الزامی است.',
            'number.unique' => 'این شماره واحد در این مرکز قبلاً ثبت شده است.',
            'type.required' => 'نوع واحد الزامی است.',
            'bed_count.required' => 'تعداد تخت الزامی است.',
            'bed_count.min' => 'تعداد تخت باید حداقل ۱ باشد.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_management' => $this->boolean('is_management', false),
        ]);
    }
}
