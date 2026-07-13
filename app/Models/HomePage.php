<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomePage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'banner_image_id',
        'banner_link',
    ];

    protected array $translatable = [
        'meta_title',
        'meta_description',
        'stats_title',
        'banner_button_text',
        'testimonials_title',
        'faq_title',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $page) {
            $page->slides()->get()->each->delete();
            $page->stats()->get()->each->delete();
            $page->testimonials()->get()->each->delete();
            $page->faqs()->get()->each->delete();
        });
    }

    public static function current(): static
    {
        return static::query()->firstOrCreate();
    }

    public function bannerImage(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'banner_image_id');
    }

    public function slides(): HasMany
    {
        return $this->hasMany(HomeSlide::class)->orderBy('position');
    }

    public function stats(): HasMany
    {
        return $this->hasMany(HomeStat::class)->orderBy('position');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->orderBy('position');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('position');
    }
}