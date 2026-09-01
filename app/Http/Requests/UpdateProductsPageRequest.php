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
            'og_image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'slides' => ['sometimes', 'array'],
            'slides.*.image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'slides.*.link' => ['nullable', 'string', 'max:2000'],
            'slides.*.translations' => ['sometimes', 'array:'.$codes->implode(',')],
            'translations' => ['sometimes', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $rules["translations.{$code}"] = ['sometimes', 'array'];
            $rules["translations.{$code}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.og_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.og_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.products_title"] = ['nullable', 'string', 'max:255'];

            $rules["slides.*.translations.{$code}"] = ['sometimes', 'array'];
            $rules["slides.*.translations.{$code}.title"] = ['nullable', 'string', 'max:500'];
            $rules["slides.*.translations.{$code}.button_text"] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }
}