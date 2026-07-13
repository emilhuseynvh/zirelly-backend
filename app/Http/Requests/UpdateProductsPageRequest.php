<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductsPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = Language::activeCached()->pluck('code');

        $rules = [
            'side_image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'slide_image_ids' => ['sometimes', 'array'],
            'slide_image_ids.*' => ['integer', 'distinct', Rule::exists('uploads', 'id')],
            'translations' => ['sometimes', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $rules["translations.{$code}"] = ['sometimes', 'array'];
            $rules["translations.{$code}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.products_title"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }
}