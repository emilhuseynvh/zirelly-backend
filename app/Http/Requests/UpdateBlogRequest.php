<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = Language::activeCached()->pluck('code');

        $rules = [
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($this->route('blog'))],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'translations' => ['sometimes', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $rules["translations.{$code}"] = ['sometimes', 'array'];
            $rules["translations.{$code}.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.content"] = ['nullable', 'string'];
        }

        return $rules;
    }
}