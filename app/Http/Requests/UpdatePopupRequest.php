<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = Language::activeCached()->pluck('code');

        $rules = [
            'image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'button_link' => ['nullable', 'string', 'max:2000'],
            'delay_seconds' => ['sometimes', 'integer', 'min:0', 'max:600'],
            'is_active' => ['sometimes', 'boolean'],
            'show_once' => ['sometimes', 'boolean'],
            'translations' => ['sometimes', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $rules["translations.{$code}"] = ['sometimes', 'array'];
            $rules["translations.{$code}.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.description"] = ['nullable', 'string', 'max:1000'];
            $rules["translations.{$code}.button_text"] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }
}
