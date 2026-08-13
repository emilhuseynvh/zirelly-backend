<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge(['phone' => Phone::normalize((string) $this->input('phone'))]);
        }
    }

    public function messages(): array
    {
        return ['phone.regex' => __('messages.phone_invalid')];
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'surname' => ['sometimes', 'required', 'string', 'max:100'],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'regex:'.Phone::AZ_PATTERN, Rule::unique('users', 'phone')->ignore($userId)],
            'birth_date' => ['sometimes', 'required', 'date', 'before:today'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', 'required', 'confirmed', Password::defaults()],
            'current_password' => ['required_with:password', 'current_password:sanctum'],
        ];
    }
}