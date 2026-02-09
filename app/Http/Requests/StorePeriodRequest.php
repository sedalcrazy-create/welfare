<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Period::class);
    }

    public function rules(): array
    {
        return [
            'center_id' => 'required|exists:centers,id',
            'season_id' => 'nullable|exists:seasons,id',
            'jalali_start_date' => 'required|string|size:10',
            'jalali_end_date' => 'required|string|size:10',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:draft,open,closed,completed',
        ];
    }

    public function messages(): array
    {
        return [
            'center_id.required' => 'انتخاب مرکز الزامی است.',
            'jalali_start_date.required' => 'تاریخ شروع الزامی است.',
            'jalali_end_date.required' => 'تاریخ پایان الزامی است.',
            'capacity.required' => 'ظرفیت الزامی است.',
            'capacity.min' => 'ظرفیت باید حداقل ۱ باشد.',
        ];
    }
}
