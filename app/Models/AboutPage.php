<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AboutPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'hero_image_id',
        'section_image_id',
        'og_image_id',
    ];

    protected array $translatable = [
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'hero_title',
        'hero_description',
        'section_title',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $page) {
            $page->items()->get()->each->delete();
        });
    }

    public static function current(): static
    {
        return static::query()->firstOrCreate();
    }

    public function heroImage(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'hero_image_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'og_image_id');
    }

    public function sectionImage(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'section_image_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AboutItem::class)->orderBy('position');
    }
}