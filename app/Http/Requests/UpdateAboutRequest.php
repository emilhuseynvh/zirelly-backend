<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAboutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = Language::activeCached()->pluck('code');

        $rules = [
            'hero_image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'section_image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'og_image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'translations' => ['sometimes', 'array:'.$codes->implode(',')],
            'items' => ['sometimes', 'array'],
            'items.*.translations' => ['required', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $rules["translations.{$code}"] = ['sometimes', 'array'];
            $rules["translations.{$code}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.og_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.og_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.hero_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.hero_description"] = ['nullable', 'string'];
            $rules["translations.{$code}.section_title"] = ['nullable', 'string', 'max:255'];

            $rules["items.*.translations.{$code}"] = ['sometimes', 'array'];
            $rules["items.*.translations.{$code}.title"] = ['nullable', 'string', 'max:255'];
            $rules["items.*.translations.{$code}.description"] = ['nullable', 'string'];
        }

        return $rules;
    }
}