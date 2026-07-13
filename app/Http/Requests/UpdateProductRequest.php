<?php

namespace App\Http\Requests;

use App\Enums\DiscountType;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = Language::activeCached()->pluck('code');

        $rules = [
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->route('product'))],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
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
            'translations' => ['sometimes', 'array:'.$codes->implode(',')],
            'features' => ['sometimes', 'array'],
            'features.*.translations' => ['required', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $rules["translations.{$code}"] = ['sometimes', 'array'];
            $rules["translations.{$code}.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.description"] = ['nullable', 'string'];

            $rules["features.*.translations.{$code}"] = ['sometimes', 'array'];
            $rules["features.*.translations.{$code}.name"] = ['nullable', 'string', 'max:255'];
            $rules["features.*.translations.{$code}.value"] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }
}