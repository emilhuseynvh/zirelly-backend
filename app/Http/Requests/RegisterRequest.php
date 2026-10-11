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

        // Bakı deyilsə inzibati rayon nəzərə alınmır
        if ($this->input('address_city') !== \App\Support\DeliveryLocations::BAKU) {
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
            'address.required' => __('messages.address_required'),
            'address_city.required' => __('messages.address_city_required'),
            'address_city.in' => __('messages.address_city_invalid'),
            'address_district.required' => __('messages.address_district_required'),
            'address_district.in' => __('messages.address_district_invalid'),
            'address_street.required' => __('messages.address_street_required'),
            'address_building.required' => __('messages.address_building_required'),
        ];
    }

    public function rules(): array
    {
        // Eyni e-poçtla təsdiqlənməmiş qeyd varsa, register onu yeniləyir —
        // telefon unikallığında məhz o sətir nəzərə alınmamalıdır. Qalan BÜTÜN
        // istifadəçilərlə yoxlanılır, çünki DB-dəki unique indeks hamını əhatə edir
        // (əvvəl yalnız təsdiqlənmişlərlə yoxlanırdı və toqquşma 500 verirdi).
        $pendingId = \App\Models\User::query()
            ->where('email', (string) $this->input('email'))
            ->whereNull('email_verified_at')
            ->value('id');

        return [
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:'.Phone::AZ_PATTERN, Rule::unique('users', 'phone')->ignore($pendingId)],
            'birth_date' => ['required', 'date', 'before:today'],
            // Vahid "address" sətri prepareForValidation-da struktur sahələrdən qurulur
            'address' => ['required', 'string', 'max:1000'],
            'address_city' => ['required', Rule::in(\App\Support\DeliveryLocations::CITIES)],
            'address_district' => [
                Rule::requiredIf(fn () => $this->input('address_city') === \App\Support\DeliveryLocations::BAKU),
                'nullable',
                Rule::in(\App\Support\DeliveryLocations::BAKU_DISTRICTS),
            ],
            'address_street' => ['required', 'string', 'max:255'],
            'address_building' => ['required', 'string', 'max:50'],
            'address_apartment' => ['nullable', 'string', 'max:50'],
            'address_note' => ['nullable', 'string', 'max:500'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->whereNotNull('email_verified_at')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}