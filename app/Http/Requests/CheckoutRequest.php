<?php

namespace App\Http\Requests;

use App\Support\DeliveryLocations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'promocode' => ['sometimes', 'nullable', 'string', 'max:50'],
            // Köhnə frontend yalnız "address" göndərir — keçid dövründə qəbul olunur
            'address' => ['required_without:address_city', 'nullable', 'string', 'max:1000'],
            'address_city' => ['required_without:address', 'nullable', Rule::in(DeliveryLocations::CITIES)],
            'address_district' => [
                Rule::requiredIf(fn () => $this->input('address_city') === DeliveryLocations::BAKU),
                'nullable',
                Rule::in(DeliveryLocations::BAKU_DISTRICTS),
            ],
            'address_street' => ['required_with:address_city', 'nullable', 'string', 'max:255'],
            'address_building' => ['required_with:address_city', 'nullable', 'string', 'max:50'],
            'address_apartment' => ['sometimes', 'nullable', 'string', 'max:50'],
            'address_note' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'address.required_without' => __('messages.address_required'),
            'address_city.required_without' => __('messages.address_city_required'),
            'address_city.in' => __('messages.address_city_invalid'),
            'address_district.required' => __('messages.address_district_required'),
            'address_district.in' => __('messages.address_district_invalid'),
            'address_street.required_with' => __('messages.address_street_required'),
            'address_building.required_with' => __('messages.address_building_required'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('promocode')) {
            $this->merge(['promocode' => strtoupper(trim((string) $this->input('promocode')))]);
        }

        // Bakı deyilsə inzibati rayon nəzərə alınmır
        if ($this->input('address_city') !== DeliveryLocations::BAKU) {
            $this->merge(['address_district' => null]);
        }
    }
}
