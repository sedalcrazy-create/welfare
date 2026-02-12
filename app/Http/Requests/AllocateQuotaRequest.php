<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocateQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['admin', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'center_id' => 'required|exists:centers,id',
            'quota_total' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'center_id.required' => 'انتخاب مرکز الزامی است',
            'quota_total.required' => 'تعداد سهمیه الزامی است',
            'quota_total.min' => 'سهمیه نمی‌تواند منفی باشد',
        ];
    }
}
