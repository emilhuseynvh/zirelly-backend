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

        // Bakı deyilsə inzibati rayon nəzərə alınmır
        if ($this->has('address_city') && $this->input('address_city') !== \App\Support\DeliveryLocations::BAKU) {
            $this->merge(['address_district' => null]);
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
        return [
            'phone.regex' => __('messages.phone_invalid'),
            'phone.unique' => __('messages.phone_taken'),
            'address_city.required' => __('messages.address_city_required'),
            'address_city.in' => __('messages.address_city_invalid'),
            'address_district.required' => __('messages.address_district_required'),
            'address_district.in' => __('messages.address_district_invalid'),
            'address_street.required_with' => __('messages.address_street_required'),
            'address_building.required_with' => __('messages.address_building_required'),
            'address_note.required_with' => __('messages.address_note_required'),
        ];
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
            // Ünvan göndərilirsə, qeydiyyatdakı kimi tam olmalıdır
            'address_city' => ['sometimes', 'required', Rule::in(\App\Support\DeliveryLocations::CITIES)],
            'address_district' => [
                Rule::requiredIf(fn () => $this->input('address_city') === \App\Support\DeliveryLocations::BAKU),
                'nullable',
                Rule::in(\App\Support\DeliveryLocations::BAKU_DISTRICTS),
            ],
            'address_street' => ['required_with:address_city', 'nullable', 'string', 'max:255'],
            'address_building' => ['required_with:address_city', 'nullable', 'string', 'max:50'],
            'address_apartment' => ['nullable', 'string', 'max:50'],
            'address_note' => ['required_with:address_city', 'nullable', 'string', 'max:500'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', 'required', 'confirmed', Password::defaults()],
            'current_password' => ['required_with:password', 'current_password:sanctum'],
        ];
    }
}