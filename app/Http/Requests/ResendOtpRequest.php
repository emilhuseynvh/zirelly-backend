<?php

namespace App\Http\Requests;

use App\Models\OtpCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'type' => ['required', Rule::in([OtpCode::TYPE_REGISTER, OtpCode::TYPE_RESET_PASSWORD])],
        ];
    }
}
