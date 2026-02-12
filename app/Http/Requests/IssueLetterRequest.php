<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personnel_id' => 'required|exists:personnel,id',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'personnel_id.required' => 'انتخاب پرسنل الزامی است',
            'personnel_id.exists' => 'پرسنل انتخاب شده یافت نشد',
        ];
    }
}
