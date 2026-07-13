<?php

namespace App\Http\Requests;

use App\Enums\DiscountType;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = Language::activeCached()->pluck('code');
        $default = Language::defaultLanguage()?->code;

        $rules = [
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')],
            'price' => ['required', 'numeric', 'min:0'],
            'discount' => [
                'nullable',
                'numeric',
                'min:0',
                $this->input('discount_type') === DiscountType::Percent->value ? 'max:100' : 'lte:price',
            ],
            'discount_type' => ['nullable', 'required_with:discount', Rule::enum(DiscountType::class)],
            'is_active' => ['sometimes', 'boolean'],
            'image_ids' => ['sometimes', 'array'],
            'image_ids.*' => ['integer', 'distinct', Rule::exists('uploads', 'id')],
            'translations' => ['required', 'array:'.$codes->implode(',')],
            'features' => ['sometimes', 'array'],
            'features.*.translations' => ['required', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $isDefault = $code === $default;

            $rules["translations.{$code}"] = [$isDefault ? 'required' : 'sometimes', 'array'];
            $rules["translations.{$code}.title"] = [$isDefault ? 'required' : 'nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.description"] = ['nullable', 'string'];

            $rules["features.*.translations.{$code}"] = [$isDefault ? 'required' : 'sometimes', 'array'];
            $rules["features.*.translations.{$code}.name"] = [$isDefault ? 'required' : 'nullable', 'string', 'max:255'];
            $rules["features.*.translations.{$code}.value"] = [$isDefault ? 'required' : 'nullable', 'string', 'max:1000'];
        }

        return $rules;
    }
}