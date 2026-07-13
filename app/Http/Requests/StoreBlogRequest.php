<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogRequest extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'translations' => ['required', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $isDefault = $code === $default;

            $rules["translations.{$code}"] = [$isDefault ? 'required' : 'sometimes', 'array'];
            $rules["translations.{$code}.title"] = [$isDefault ? 'required' : 'nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.content"] = [$isDefault ? 'required' : 'nullable', 'string'];
        }

        return $rules;
    }
}