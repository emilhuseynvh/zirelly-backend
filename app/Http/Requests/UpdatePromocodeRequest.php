<?php

namespace App\Http\Requests;

use App\Enums\DiscountType;
use App\Enums\PromocodeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromocodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => [
                'sometimes', 'string', 'max:50', 'alpha_dash',
                Rule::unique('promocodes', 'code')->ignore($this->route('promocode')),
            ],
            'type' => ['sometimes', Rule::enum(PromocodeType::class)],
            'discount_type' => ['sometimes', Rule::enum(DiscountType::class)],
            'discount_value' => ['sometimes', 'numeric', 'min:0.01', 'max:99999999'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
        }
    }
}
