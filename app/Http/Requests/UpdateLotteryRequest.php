<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLotteryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('lottery'));
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'jalali_registration_start' => 'required|string|size:10',
            'jalali_registration_end' => 'required|string|size:10',
            'jalali_draw_date' => 'required|string|size:10',
            'algorithm' => 'required|in:weighted_random,priority_based',
            'status' => 'required|in:draft,open,closed',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الزامی است.',
            'jalali_registration_start.required' => 'تاریخ شروع ثبت‌نام الزامی است.',
            'jalali_registration_end.required' => 'تاریخ پایان ثبت‌نام الزامی است.',
            'jalali_draw_date.required' => 'تاریخ قرعه‌کشی الزامی است.',
        ];
    }
}
