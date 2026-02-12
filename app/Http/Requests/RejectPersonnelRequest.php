<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectPersonnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['admin', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => 'required|string|min:10|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'دلیل رد درخواست الزامی است',
            'rejection_reason.min' => 'دلیل رد حداقل باید 10 کاراکتر باشد',
            'rejection_reason.max' => 'دلیل رد حداکثر 500 کاراکتر می‌تواند باشد',
        ];
    }
}
