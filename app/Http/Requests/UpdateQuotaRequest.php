<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['admin', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'quota_total' => 'required|integer|min:0|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'quota_total.required' => 'تعداد سهمیه الزامی است',
            'quota_total.integer' => 'سهمیه باید عدد صحیح باشد',
            'quota_total.min' => 'سهمیه نمی‌تواند منفی باشد',
            'quota_total.max' => 'سهمیه نمی‌تواند بیشتر از 1000 باشد',
        ];
    }
}
