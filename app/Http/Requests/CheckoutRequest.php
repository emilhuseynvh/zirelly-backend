<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'promocode' => ['sometimes', 'nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('promocode')) {
            $this->merge(['promocode' => strtoupper(trim((string) $this->input('promocode')))]);
        }
    }
}
