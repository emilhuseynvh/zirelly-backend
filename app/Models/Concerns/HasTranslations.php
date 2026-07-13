<?php

namespace App\Models\Concerns;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    public static function bootHasTranslations(): void
    {
        static::deleted(function (Model $model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->translations()->delete();
        });
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function translatedFields(): array
    {
        return $this->translatable ?? [];
    }

    public function translate(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        $value = $this->translationValue($field, $locale);

        if ($value === null) {
            $default = Language::defaultLanguage()?->code;

            if ($default !== null && $default !== $locale) {
                $value = $this->translationValue($field, $default);
            }
        }

        return $value;
    }

    public function syncTranslations(array $translations): static
    {
        $now = now();
        $rows = [];

        foreach ($translations as $code => $fields) {
            $language = Language::byCode($code);

            if ($language === null || ! is_array($fields)) {
                continue;
            }

            $known = array_intersect_key($fields, array_flip($this->translatedFields()));

            foreach ($known as $field => $value) {
                $rows[] = [
                    'translatable_type' => $this->getMorphClass(),
                    'translatable_id' => $this->getKey(),
                    'language_id' => $language->getKey(),
                    'field' => $field,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            Translation::query()->upsert(
                $rows,
                ['translatable_type', 'translatable_id', 'language_id', 'field'],
                ['value', 'updated_at'],
            );

            $this->unsetRelation('translations');
        }

        return $this;
    }

    public function translationsGrouped(): array
    {
        $grouped = [];

        foreach (Language::activeCached() as $language) {
            foreach ($this->translatedFields() as $field) {
                $grouped[$language->code][$field] = $this->translationValue($field, $language->code);
            }
        }

        return $grouped;
    }

    protected function translationValue(string $field, string $code): ?string
    {
        $language = Language::byCode($code);

        if ($language === null) {
            return null;
        }

        return $this->translations
            ->first(fn (Translation $t) => $t->field === $field && $t->language_id === $language->getKey())
            ?->value;
    }

    public function getAttribute($key)
    {
        if (
            $key !== null
            && in_array($key, $this->translatedFields(), true)
            && ! array_key_exists($key, $this->attributes)
        ) {
            return $this->translate($key);
        }

        return parent::getAttribute($key);
    }
}