<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => 'required|string|min:10|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'دلیل لغو معرفی‌نامه الزامی است',
            'cancellation_reason.min' => 'دلیل لغو حداقل باید 10 کاراکتر باشد',
            'cancellation_reason.max' => 'دلیل لغو حداکثر 500 کاراکتر می‌تواند باشد',
        ];
    }
}
