<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Language extends Model
{
    protected static ?Collection $cached = null;

    protected $attributes = [
        'is_default' => false,
        'is_active' => true,
        'position' => 0,
    ];

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'is_default',
        'is_active',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $language) {
            if ($language->is_default) {
                static::query()
                    ->whereKeyNot($language->getKey())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            static::flushCache();
        });

        static::deleted(fn () => static::flushCache());
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    /** @return Collection<int, static> */
    public static function allCached(): Collection
    {
        return static::$cached ??= static::query()
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, static> */
    public static function activeCached(): Collection
    {
        return static::allCached()->where('is_active', true)->values();
    }

    public static function byCode(?string $code): ?static
    {
        return $code === null
            ? null
            : static::allCached()->firstWhere('code', $code);
    }

    public static function defaultLanguage(): ?static
    {
        return static::allCached()->firstWhere('is_default', true)
            ?? static::allCached()->first();
    }

    public static function flushCache(): void
    {
        static::$cached = null;
    }
}
