<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = Language::activeCached()->pluck('code');

        $rules = [
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'map_embed_url' => ['nullable', 'string', 'max:2000'],
            'facebook_url' => ['nullable', 'string', 'max:500'],
            'instagram_url' => ['nullable', 'string', 'max:500'],
            'tiktok_url' => ['nullable', 'string', 'max:500'],
            'linkedin_url' => ['nullable', 'string', 'max:500'],
            'translations' => ['sometimes', 'array:'.$codes->implode(',')],
        ];

        foreach ($codes as $code) {
            $rules["translations.{$code}"] = ['sometimes', 'array'];
            $rules["translations.{$code}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.subtitle"] = ['nullable', 'string', 'max:1000'];
            $rules["translations.{$code}.footer_description"] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }
}