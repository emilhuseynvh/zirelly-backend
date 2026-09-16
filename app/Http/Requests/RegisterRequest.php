<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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

        if ($this->filled('address_city') && $this->filled('address_street') && $this->filled('address_building')) {
            $composed = \App\Support\DeliveryLocations::compose(
                (string) $this->input('address_city'),
                $this->filled('address_district') ? (string) $this->input('address_district') : null,
                (string) $this->input('address_street'),
                (string) $this->input('address_building'),
                $this->filled('address_apartment') ? (string) $this->input('address_apartment') : null,
            );
            $this->merge(['address' => $composed]);
        }
    }

    public function messages(): array
    {
        return ['phone.regex' => __('messages.phone_invalid')];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:'.Phone::AZ_PATTERN, Rule::unique('users', 'phone')->whereNotNull('email_verified_at')],
            'birth_date' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:1000'],
            'address_city' => ['sometimes', 'required', 'string', 'max:100'],
            'address_district' => ['nullable', 'string', 'max:100'],
            'address_street' => ['sometimes', 'required', 'string', 'max:255'],
            'address_building' => ['sometimes', 'required', 'string', 'max:50'],
            'address_apartment' => ['nullable', 'string', 'max:50'],
            'address_note' => ['nullable', 'string', 'max:500'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->whereNotNull('email_verified_at')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}