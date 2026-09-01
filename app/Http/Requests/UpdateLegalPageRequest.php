<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLegalPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = Language::activeCached()->pluck('code');

        $rules = [
            'og_image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'translations' => ['required', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $rules["translations.{$code}"] = ['sometimes', 'array'];
            $rules["translations.{$code}.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.content"] = ['nullable', 'string', 'max:100000'];
            $rules["translations.{$code}.og_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.og_description"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }
}
