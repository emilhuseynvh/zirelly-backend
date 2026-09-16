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
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'surname' => ['sometimes', 'required', 'string', 'max:100'],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'regex:'.Phone::AZ_PATTERN, Rule::unique('users', 'phone')->ignore($userId)],
            'birth_date' => ['sometimes', 'required', 'date', 'before:today'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'address_city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'address_district' => ['nullable', 'string', 'max:100'],
            'address_street' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_building' => ['sometimes', 'nullable', 'string', 'max:50'],
            'address_apartment' => ['nullable', 'string', 'max:50'],
            'address_note' => ['nullable', 'string', 'max:500'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', 'required', 'confirmed', Password::defaults()],
            'current_password' => ['required_with:password', 'current_password:sanctum'],
        ];
    }
}