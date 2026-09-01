<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = Language::activeCached()->pluck('code');
        $langArray = 'array:'.$codes->implode(',');

        $rules = [
            'banner_image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'og_image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'banner_link' => ['nullable', 'string', 'max:255'],
            'translations' => ['sometimes', $langArray],

            'slides' => ['sometimes', 'array'],
            'slides.*.image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'slides.*.link' => ['nullable', 'string', 'max:255'],
            'slides.*.translations' => ['sometimes', $langArray],

            'stats' => ['sometimes', 'array'],
            'stats.*.value' => ['required', 'string', 'max:50'],
            'stats.*.translations' => ['sometimes', $langArray],

            'testimonials' => ['sometimes', 'array'],
            'testimonials.*.name' => ['required', 'string', 'max:255'],
            'testimonials.*.rating' => ['sometimes', 'integer', 'between:1,5'],
            'testimonials.*.image_id' => ['nullable', 'integer', Rule::exists('uploads', 'id')],
            'testimonials.*.translations' => ['sometimes', $langArray],

            'faqs' => ['sometimes', 'array'],
            'faqs.*.translations' => ['required', $langArray],
        ];

        foreach ($codes as $code) {
            $rules["translations.{$code}"] = ['sometimes', 'array'];
            $rules["translations.{$code}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.og_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.og_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$code}.stats_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.banner_button_text"] = ['nullable', 'string', 'max:100'];
            $rules["translations.{$code}.testimonials_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$code}.faq_title"] = ['nullable', 'string', 'max:255'];

            $rules["slides.*.translations.{$code}"] = ['sometimes', 'array'];
            $rules["slides.*.translations.{$code}.title"] = ['nullable', 'string', 'max:255'];
            $rules["slides.*.translations.{$code}.description"] = ['nullable', 'string'];
            $rules["slides.*.translations.{$code}.button_text"] = ['nullable', 'string', 'max:100'];

            $rules["stats.*.translations.{$code}"] = ['sometimes', 'array'];
            $rules["stats.*.translations.{$code}.label"] = ['nullable', 'string', 'max:255'];

            $rules["testimonials.*.translations.{$code}"] = ['sometimes', 'array'];
            $rules["testimonials.*.translations.{$code}.comment"] = ['nullable', 'string', 'max:2000'];

            $rules["faqs.*.translations.{$code}"] = ['sometimes', 'array'];
            $rules["faqs.*.translations.{$code}.question"] = ['nullable', 'string', 'max:500'];
            $rules["faqs.*.translations.{$code}.answer"] = ['nullable', 'string'];
        }

        return $rules;
    }
}